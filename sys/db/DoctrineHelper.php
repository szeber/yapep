<?php
class sys_db_DoctrineHelper {
	static function getFullObject(ObjectData  $object) {
		if ($object instanceof DocLinkData) {
			return self::getLinkFullObject($object);
		}
		$objectType = $object->getTable()->getConnection()->queryOne('FROM ObjectTypeData WHERE id='.(int)$object['object_type_id']);
		$fullObject = $object->getTable ()->getConnection ()->getTable ($objectType['persist_class'])->find ($object->id);
		if (!$fullObject) {
			return null;
		}
		$fullObject->mapValue ('Object', $object);
		return $fullObject;
	}

	static function getLinkFullObject(DocLinkData $linkObject) {
		$docType = $linkObject->getTable ()->getConnection ()->queryOne('FROM ObjectTypeData WHERE short_name = \'document\'');
		$docObject = $linkObject->getTable ()->getConnection ->queryOne('FROM '.$docType['persist_class'].' d INNER JOIN d.Object WHERE d.id = '.$linkObject['doc_id']);
		self::getFullObject($docObject['Object']);
		$linkObject->mapValue('DocObject', $docObject);
		return $docObject;
	}

	static function mapObject(ObjectData $object) {
		$object2 = $object->getTable ()->getConnection ()->getTable ('ObjectData')->find ($object->id);
		$object->mapValue ('Object', $object2);
	}

	static function mapFullObject(ObjectData $object) {
		$object->mapValue('FullObject', self::getFullObject($object));
	}
}
?>