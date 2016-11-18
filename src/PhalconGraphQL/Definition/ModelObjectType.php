<?php

namespace PhalconGraphQL\Definition;

use Phalcon\Db\Column;
use Phalcon\Di;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\MetaData;
use Phalcon\Mvc\Model\RelationInterface;
use PhalconGraphQL\Constants\Services;
use PhalconGraphQL\Utils;

class ModelObjectType extends ObjectType
{
    protected $_modelClass;
    protected $_built = false;

    protected $_excludedFields = [];
    protected $_excludeRelations = false;
    protected $_embedRelations = false;

    protected $di;

    public function __construct($modelClass, $name=null, $description=null)
    {
        // Use class name if name not provided
        if($name === null) {
            $name = Utils::getShortClass($modelClass);
        }

        parent::__construct($name, $description);

        $this->_modelClass = $modelClass;

        $this->di = Di::getDefault();
    }

    public function exclude($field)
    {
        return $this->removeField($field);
    }

    public function excludeRelations($excludeRelations = true)
    {
        $this->_excludeRelations = $excludeRelations;
        $this->_built = false;

        return $this;
    }

    public function embedRelations($embedRelations = true)
    {
        $this->_embedRelations = $embedRelations;
        $this->_built = false;

        return $this;
    }

    public function getFields()
    {
        // Delay building, build when the fields are queried
        if(!$this->_built){

            $this->build();
            $this->_built = true;
        }

        return parent::getFields();
    }

    public function field(Field $field)
    {
        parent::field($field);
        $this->_built = false;

        return $this;
    }

    public function removeField($fieldName)
    {
        $this->_excludedFields[] = $fieldName;

        return parent::removeField($fieldName);
    }

    protected function build()
    {
        /** @var MetaData $modelsMetadata */
        $modelsMetadata = $this->di->get(Services::MODELS_METADATA);

        /** @var Manager $modelsManager */
        $modelsManager = $this->di->get(Services::MODELS_MANAGER);

        $modelClass = $this->_modelClass;
        $model = new $modelClass();

        $originalFields = $this->_fields;
        $newFields = [];

        // Attributes
        $columnMap = $modelsMetadata->getColumnMap($model);
        $dataTypes = $modelsMetadata->getDataTypes($model);
        $nonNullAttributes = $modelsMetadata->getNotNullAttributes($model);
        $identityField = $modelsMetadata->getIdentityField($model);

        $skip = $this->_excludedFields;
        $typeMap = [];

        if(method_exists($model, 'excludedFields')){
            $skip = array_merge($skip, $model->excludedFields());
        }

        if(method_exists($model, 'typeMap')){
            $typeMap = $model->typeMap();
        }

        $mappedDataTypes = [];
        $mappedNonNullAttributes = [];

        foreach ($dataTypes as $attributeName => $dataType) {

            $mappedAttributeName = is_array($columnMap) && array_key_exists($attributeName, $columnMap) ? $columnMap[$attributeName] : $attributeName;

            $type = null;
            if($attributeName == $identityField){
                $type = Types::ID;
            }
            else if(array_key_exists($mappedAttributeName, $typeMap)){
                $type = $typeMap[$mappedAttributeName];
            }
            else {
                $type = $this->getMappedDatabaseType($dataType);
            }

            $mappedDataTypes[$mappedAttributeName] = $type;

            if(in_array($attributeName, $nonNullAttributes)){
                $mappedNonNullAttributes[] = $mappedAttributeName;
            }
        }

        foreach($mappedDataTypes as $attribute => $type){

            if(in_array($attribute, $skip) || $this->fieldExists($attribute)){
                continue;
            }

            $field = Field::factory($attribute, $type);
            if(in_array($attribute, $mappedNonNullAttributes)){
                $field->nonNull();
            }

            $newFields[] = $field;
        }

        // Relations
        if(!$this->_excludeRelations) {

            /** @var RelationInterface $relation */
            foreach ($modelsManager->getRelations($modelClass) as $relation) {

                $referencedModelClass = Utils::getShortClass($relation->getReferencedModel());

                $options = $relation->getOptions();
                $relationName = is_array($options) && array_key_exists('alias',
                    $options) ? $options['alias'] : $referencedModelClass;
                $isList = in_array($relation->getType(), [Model\Relation::HAS_MANY, Model\Relation::HAS_MANY_THROUGH]);

                $field = Field::factory(lcfirst($relationName), $referencedModelClass)
                    ->isList($isList);

                if($this->_embedRelations){
                    $field->embed();
                }

                $newFields[] = $field;
            }
        }

        $this->_fields = array_merge($newFields, $originalFields);
    }

    protected function getMappedDatabaseType($type)
    {
        $responseType = null;

        switch ($type) {

            case Column::TYPE_INTEGER:
            case Column::TYPE_BIGINTEGER: {

                $responseType = Types::INT;
                break;
            }

            case Column::TYPE_DECIMAL:
            case Column::TYPE_DOUBLE:
            case Column::TYPE_FLOAT: {

                $responseType = Types::FLOAT;
                break;
            }

            case Column::TYPE_BOOLEAN: {

                $responseType = Types::BOOLEAN;
                break;
            }

            case Column::TYPE_VARCHAR:
            case Column::TYPE_CHAR:
            case Column::TYPE_TEXT:
            case Column::TYPE_BLOB:
            case Column::TYPE_MEDIUMBLOB:
            case Column::TYPE_LONGBLOB: {

                $responseType = Types::STRING;
                break;
            }

            // TODO: Implement?
//            case Column::TYPE_DATE:
//            case Column::TYPE_DATETIME: {
//
//                $responseType = self::TYPE_DATE;
//                break;
//            }

            default:
                $responseType = Types::STRING;
        }

        return $responseType;
    }

    /**
     * @return static
     */
    public static function factory($modelClass, $name=null, $description=null)
    {
        return new ModelObjectType($modelClass, $name, $description);
    }
}