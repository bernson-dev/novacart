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


# 2. Account/user models hash the exact submitted authentication password.
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


# CLI installer data is already literal command-line input; HTML decoding there
# changes the password and is incorrect.
path = 'upload/install/cli_install.php'
text = read(path)
text = re.sub(
    r"password_hash\(html_entity_decode\(\$data\['password'\],\s*ENT_QUOTES,\s*'UTF-8'\),\s*PASSWORD_DEFAULT\)",
    "password_hash($data['password'], PASSWORD_DEFAULT)",
    text,
)
write(path, text)


# HTTP installer DB credentials must remain literal. The install model receives
# cleaned $data, so use the raw request value when available.
path = 'upload/install/model/install/install.php'
text = read(path)
text = re.sub(
    r"html_entity_decode\(\$data\['db_password'\],\s*ENT_QUOTES,\s*'UTF-8'\)",
    "$this->request->getRawPost('db_password', $data['db_password'])",
    text,
)
write(path, text)


# 3. Login libraries verify the literal password. SHA1+salt and MD5 remain only
#    as migration fallbacks for imported old dumps and are immediately rehashed.
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

    # Old OpenCart dumps often have VARCHAR(40). Widen it only when a verified
    # password is actually going to be replaced by PASSWORD_DEFAULT.
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


# 4. Every HTTP authentication-password validation path uses raw password and
#    exact confirm. This covers account/register, checkout/register, affiliate,
#    admin profile/user/customer/reset and installer forms without relying on a
#    hard-coded controller list.
for p in root.joinpath('upload').rglob('*.php'):
    path = p.as_posix()
    text = read(path)
    original = text

    text = re.sub(
        r"html_entity_decode\(\$this->request->post\['password'\],\s*ENT_QUOTES,\s*'UTF-8'\)",
        "$this->request->getRawPost('password')",
        text,
    )
    text = re.sub(
        r"html_entity_decode\(\$this->request->post\['confirm'\],\s*ENT_QUOTES,\s*'UTF-8'\)",
        "$this->request->getRawPost('confirm')",
        text,
    )

    # Length validation over the exact input.
    text = text.replace(
        "utf8_strlen($this->request->post['password'])",
        "utf8_strlen($this->request->getRawPost('password'))",
    )

    # Exact password confirmation.
    text = text.replace(
        "$this->request->post['password'] != $this->request->post['confirm']",
        "$this->request->getRawPost('password') !== $this->request->getRawPost('confirm')",
    )
    text = text.replace(
        "$this->request->post['password'] !== $this->request->post['confirm']",
        "$this->request->getRawPost('password') !== $this->request->getRawPost('confirm')",
    )

    # Reject leading/trailing ASCII whitespace at create/change/reset time.
    # Login itself never trims or normalizes.
    rx = re.compile(
        r"if \(\(utf8_strlen\(\$this->request->getRawPost\('password'\)\) < ([^)]+)\) \|\| \(utf8_strlen\(\$this->request->getRawPost\('password'\)\) > ([^)]+)\)\) \{"
    )
    text = rx.sub(
        r"if (($this->request->getRawPost('password') !== trim($this->request->getRawPost('password'))) || (utf8_strlen($this->request->getRawPost('password')) < \1) || (utf8_strlen($this->request->getRawPost('password')) > \2)) {",
        text,
    )

    if text != original:
        write(path, text)


# 5. HTTP installer DB password: preserve the exact credential in direct DB
#    connection checks as well.
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


# 6. Sanity checks are deliberately scoped to authentication passwords and DB
#    credentials. SMTP keeps its existing encode/decode storage contract in this
#    branch so existing stored SMTP credentials are not silently reinterpreted.
offenders = []
for p in root.joinpath('upload').rglob('*.php'):
    for line_no, line in enumerate(p.read_text(encoding='utf-8').splitlines(), 1):
        if 'html_entity_decode' in line and (
            "$this->request->post['password']" in line
            or "$this->request->post['confirm']" in line
            or "$data['password']" in line
            or "$data['db_password']" in line
        ):
            offenders.append(f'{p.as_posix()}:{line_no}: {line.strip()}')

if offenders:
    raise RuntimeError('Remaining authentication password decodes:\n' + '\n'.join(offenders))

# Remove temporary patch machinery from the resulting source commit.
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
