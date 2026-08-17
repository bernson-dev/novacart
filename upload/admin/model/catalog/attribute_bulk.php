<?php
class ModelCatalogAttributeBulk extends Model {
	protected $languages = null;

	public function getLanguages() {
		if ($this->languages === null) {
			$this->languages = $this->db->query(
				"SELECT * FROM " . DB_PREFIX . "language WHERE status = '1' ORDER BY sort_order, name"
			)->rows;
		}
		return $this->languages;
	}

	public function getAttributeGroups($language_id) {
		$sql = "
            SELECT ag.attribute_group_id, agd.name
            FROM " . DB_PREFIX . "attribute_group ag
            JOIN " . DB_PREFIX . "attribute_group_description agd
              ON ag.attribute_group_id = agd.attribute_group_id
            WHERE agd.language_id = " . (int)$language_id . "
            ORDER BY agd.name
        ";
		return $this->db->query($sql)->rows;
	}

	public function attributeExists($name, $group_id) {
		$query = $this->db->query(
			"SELECT 1 FROM " . DB_PREFIX . "attribute a
             JOIN " . DB_PREFIX . "attribute_description ad
               ON a.attribute_id = ad.attribute_id
             WHERE ad.name = '" . $this->db->escape($name) . "'
               AND a.attribute_group_id = " . (int)$group_id . "
             LIMIT 1"
		);
		return (bool)$query->num_rows;
	}

	public function addAttributeBulk(array $names, int $group_id, array $languages) {
		// Начинаем транзакцию
		$this->db->query("START TRANSACTION");

		// 1) Вставляем атрибут
		$this->db->query(
			"INSERT INTO " . DB_PREFIX . "attribute
             SET attribute_group_id = " . (int)$group_id . ",
                 sort_order = 0"
		);
		$attribute_id = $this->db->getLastId();

		if (!$attribute_id) {
			$this->db->query("ROLLBACK");
			return false;
		}

		// 2) Вставляем описания
		foreach ($languages as $language) {
			$lang_id = (int)$language['language_id'];
			$attribute_name = $names[$lang_id] ?? reset($names);

			if (trim($attribute_name) !== '') {
				$this->db->query(
					"INSERT INTO " . DB_PREFIX . "attribute_description
                     SET attribute_id = " . $attribute_id . ",
                         language_id  = " . $lang_id . ",
                         name         = '" . $this->db->escape($attribute_name) . "'"
				);
			}
		}

		// Фиксируем транзакцию
		$this->db->query("COMMIT");

		return $attribute_id;
	}
}
