from pathlib import Path
import re

root = Path('.')
changed = set()


def read(path):
    return (root / path).read_text(encoding='utf-8')


def write(path, text):
    p = root / path
    old = p.read_text(encoding='utf-8')
    if text != old:
        p.write_text(text, encoding='utf-8', newline='')
        changed.add(path)


# 1. Keep historical OpenCart cleaning for normal fields and expose raw POST
#    explicitly for opaque credentials.
path = 'upload/system/library/request.php'
text = read(path)
if 'function getRawPost(' not in text:
    marker = "\n\t/**\n\t * @param\tarray\t$data\n"
    method = r'''
	/**
	 * Return an original scalar POST value without trim() or HTML encoding.
	 *
	 * Intended for passwords and other opaque credentials only.
	 * Ordinary request data must continue to use $this->post.
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function getRawPost($key, $default = '') {
		if (!array_key_exists($key, $_POST) || !is_string($_POST[$key])) {
			return $default;
		}

		return $_POST[$key];
	}
'''
    if marker not in text:
        raise RuntimeError('Request insertion marker not found')
    text = text.replace(marker, '\n' + method + marker, 1)
    write(path, text)


# 2. Core password models hash the exact submitted password.
for path in [
    'upload/catalog/model/account/customer.php',
    'upload/admin/model/customer/customer.php',
    'upload/admin/model/user/user.php',
    'upload/install/model/install/install.php',
]:
    text = read(path)
    text = re.sub(
        r"password_hash\(html_entity_decode\(\$data\['password'\],\s*ENT_QUOTES,\s*'UTF-8'\),\s*PASSWORD_DEFAULT\)",
        "password_hash($this->request->getRawPost('password', $data['password']), PASSWORD_DEFAULT)",
        text,
    )
    text = re.sub(
        r"password_hash\(html_entity_decode\(\$password,\s*ENT_QUOTES,\s*'UTF-8'\),\s*PASSWORD_DEFAULT\)",
        "password_hash($this->request->getRawPost('password', $password), PASSWORD_DEFAULT)",
        text,
    )
    write(path, text)


# 3. Login libraries always verify the literal password. They retain SHA1+salt
#    and MD5 fallbacks for old dumps, then rehash immediately after success.
for path, table in [
    ('upload/system/library/cart/customer.php', 'customer'),
    ('upload/system/library/cart/user.php', 'user'),
]:
    text = read(path)

    login_pos = text.find('\tpublic function login(')
    if login_pos < 0:
        raise RuntimeError('login() not found in ' + path)
    brace = text.find('{', login_pos)
    if brace < 0:
        raise RuntimeError('login() opening brace not found in ' + path)

    raw_block = "\n\t\t$raw_password = $this->request->getRawPost('password', null);\n\n\t\tif ($raw_password !== null) {\n\t\t\t$password = $raw_password;\n\t\t}\n"
    if "$raw_password = $this->request->getRawPost('password', null);" not in text:
        text = text[:brace + 1] + raw_block + text[brace + 1:]

    # Timing-safe legacy comparisons.
    text = re.sub(
        r"\$([a-z_]+)_query->row\['password'\]\s*==\s*sha1\(([^\n]+)\)",
        r"hash_equals($\1_query->row['password'], sha1(\2))",
        text,
    )
    text = re.sub(
        r"\$([a-z_]+)_query->row\['password'\]\s*==\s*md5\(\$password\)",
        r"hash_equals($\1_query->row['password'], md5($password))",
        text,
    )

    # Old OpenCart dumps commonly use VARCHAR(40). Expand only when a successful
    # login is about to persist a modern PASSWORD_DEFAULT hash.
    if 'private function ensurePasswordColumn()' not in text:
        helper = f'''\n\tprivate function ensurePasswordColumn() {{\n\t\t$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "{table}` LIKE 'password'");\n\n\t\tif ($query->num_rows && isset($query->row['Type']) && preg_match('/^(?:var)?char\\((\\d+)\\)$/i', $query->row['Type'], $matches) && (int)$matches[1] < 255) {{\n\t\t\t$this->db->query("ALTER TABLE `" . DB_PREFIX . "{table}` MODIFY `password` VARCHAR(255) NOT NULL");\n\t\t}}\n\t}}\n\n'''
        marker = '\tpublic function logout()'
        if marker not in text:
            raise RuntimeError('logout marker not found in ' + path)
        text = text.replace(marker, helper + marker, 1)

    text = text.replace(
        '\t\t\tif ($rehash) {\n\t\t\t\t$this->db->query(',
        '\t\t\tif ($rehash) {\n\t\t\t\t$this->ensurePasswordColumn();\n\n\t\t\t\t$this->db->query(',
        1,
    )
    write(path, text)


# 4. Admin login no longer entity-decodes the password before the User library.
path = 'upload/admin/controller/common/login.php'
text = read(path)
text = text.replace(
    "html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8')",
    "$this->request->getRawPost('password')",
)
write(path, text)


# 5. Customer and admin password forms validate the exact raw password.
validation_paths = [
    'upload/catalog/controller/account/register.php',
    'upload/catalog/controller/account/password.php',
    'upload/catalog/controller/account/reset.php',
    'upload/catalog/controller/checkout/register.php',
    'upload/admin/controller/customer/customer.php',
    'upload/admin/controller/user/user.php',
    'upload/admin/controller/common/reset.php',
]

for path in validation_paths:
    text = read(path)

    # Entity decoding is no longer part of password length validation.
    text = re.sub(
        r"utf8_strlen\(html_entity_decode\(\$this->request->post\['password'\],\s*ENT_QUOTES,\s*'UTF-8'\)\)",
        "utf8_strlen($this->request->getRawPost('password'))",
        text,
    )
    text = text.replace(
        "utf8_strlen($this->request->post['password'])",
        "utf8_strlen($this->request->getRawPost('password'))",
    )

    # Confirm is always exact; no decoding/canonicalisation.
    text = re.sub(
        r"html_entity_decode\(\$this->request->post\['password'\],\s*ENT_QUOTES,\s*'UTF-8'\)\s*!=\s*html_entity_decode\(\$this->request->post\['confirm'\],\s*ENT_QUOTES,\s*'UTF-8'\)",
        "$this->request->getRawPost('password') !== $this->request->getRawPost('confirm')",
        text,
    )
    text = text.replace(
        "$this->request->post['password'] != $this->request->post['confirm']",
        "$this->request->getRawPost('password') !== $this->request->getRawPost('confirm')",
    )
    text = text.replace(
        "$this->request->post['password'] !== $this->request->post['confirm']",
        "$this->request->getRawPost('password') !== $this->request->getRawPost('confirm')",
    )

    # Add edge-whitespace rejection to existing password length checks.
    rx = re.compile(
        r"if \(\(utf8_strlen\(\$this->request->getRawPost\('password'\)\) < ([^)]+)\) \|\| \(utf8_strlen\(\$this->request->getRawPost\('password'\)\) > ([^)]+)\)\) \{"
    )
    text = rx.sub(
        r"if (($this->request->getRawPost('password') !== trim($this->request->getRawPost('password'))) || (utf8_strlen($this->request->getRawPost('password')) < \1) || (utf8_strlen($this->request->getRawPost('password')) > \2)) {",
        text,
    )

    write(path, text)


# 6. Installer DB password is an opaque external credential. Do not trim or HTML
#    encode/decode it; external DB credentials must match literally.
path = 'upload/install/controller/install/step_3.php'
text = read(path)
text = text.replace(
    "html_entity_decode($this->request->post['db_password'], ENT_QUOTES, 'UTF-8')",
    "$this->request->getRawPost('db_password')",
)
text = text.replace(
    "$this->request->post['db_password']",
    "$this->request->getRawPost('db_password')",
)
write(path, text)


# 7. Settings whose key contains "password" (SMTP and extension credentials)
#    are persisted from raw POST while all other setting fields retain historical
#    Request cleaning.
path = 'upload/admin/model/setting/setting.php'
text = read(path)
marker = "\t\tforeach ($data as $key => $value) {\n"
addition = "\t\tforeach ($data as $key => $value) {\n\t\t\tif (!is_array($value) && stripos($key, 'password') !== false) {\n\t\t\t\t$value = $this->request->getRawPost($key, $value);\n\t\t\t}\n\n"
if addition not in text:
    if marker not in text:
        raise RuntimeError('Setting foreach marker not found')
    text = text.replace(marker, addition, 1)
write(path, text)


# 8. Sanity checks.
offenders = []
for p in root.joinpath('upload').rglob('*.php'):
    for line_no, line in enumerate(p.read_text(encoding='utf-8').splitlines(), 1):
        if 'html_entity_decode' in line and ('password' in line or 'confirm' in line):
            offenders.append(f'{p.as_posix()}:{line_no}: {line.strip()}')

if offenders:
    raise RuntimeError('Remaining password entity decodes:\n' + '\n'.join(offenders))

# Remove the temporary patch machinery from the resulting source commit.
for temp in [
    '.github/workflows/apply-password-processing-fix.yml',
    '.github/scripts/apply-password-fix.py',
]:
    p = root / temp
    if p.exists():
        p.unlink()
        changed.add(temp)

print('Changed files:')
for path in sorted(changed):
    print(' -', path)
