<?php

namespace Bigfork\SilverstripeUserFormsTidying\Forms;

use Exception;
use LogicException;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataObjectInterface;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\ORM\ManyManyThroughList;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\EditableFormField\EditableTextField;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use Symbiote\GridFieldExtensions\GridFieldExtensions;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * Much of this class is copied from GridFieldAddNewInlineButton. It accomplishes two things:
 *
 * 1. When a record is added "inline", the class name dropdown defaults to EditableTextField
 * 2. Fixes an issue in handleSave() where the default add new inline button tries to save the
 *    record as an EditableFormField instead of the chosen class from the dropdown
 */
class GridFieldAddNewFormFieldInlineButton extends GridFieldAddNewInlineButton
{
    public function getHTMLFragments($grid)
    {
        if ($grid->getList() && !singleton($grid->getModelClass())->canCreate()) {
            return array();
        }

        $fragment = $this->getFragment();

        /** @var GridFieldEditableColumns $editable */
        $editable = $grid->getConfig()->getComponentByType(GridFieldEditableColumns::class);
        if (!$editable) {
            throw new Exception('Inline adding requires the editable columns component');
        }

        Requirements::javascript('symbiote/silverstripe-gridfieldextensions:javascript/tmpl.js');
        GridFieldExtensions::include_requirements();

        $data = ArrayData::create(array(
            'Title'  => $this->getTitle(),
        ));

        return array(
            $fragment => $data->renderWith(__CLASS__),
            'after'   => $this->getRowTemplate($grid, $editable)
        );
    }

    private function getRowTemplate(GridField $grid, GridFieldEditableColumns $editable)
    {
        $columns = ArrayList::create();
        $handled = array_keys($editable->getDisplayFields($grid) ?? []);

        if ($grid->getList()) {
            $record = Injector::inst()->create($grid->getModelClass());
        } else {
            $record = null;
        }

        $fields = $editable->getFields($grid, $record);

        foreach ($grid->getColumns() as $column) {
            if (in_array($column, $handled ?? [])) {
                $field = $fields->dataFieldByName($column);
                $field->setName(sprintf(
                    '%s[%s][{%%=o.num%%}][%s]',
                    $grid->getName(),
                    self::POST_KEY,
                    $field->getName()
                ));

                if ($record && $record->hasField($column)) {
                    $value = $record->getField($column);

                    // Ensures the class name dropdown defaults to text field
                    if ($value === EditableFormField::class && $column === 'ClassName') {
                        $value = EditableTextField::class;
                    }

                    $field->setValue($value);
                }
                $content = $field->Field();
            } else {
                $content = $grid->getColumnContent($record, $column);

                // Convert GridFieldEditableColumns to the template format
                $content = str_replace(
                    sprintf('[%s][0]', GridFieldEditableColumns::POST_KEY),
                    sprintf('[%s][{%%=o.num%%}]', self::POST_KEY),
                    $content ?? ''
                );
            }

            // Cast content
            if (! $content instanceof DBField) {
                $content = DBField::create_field('HTMLFragment', $content);
            }

            $attrs = '';

            foreach ($grid->getColumnAttributes($record, $column) as $attr => $val) {
                $attrs .= sprintf(' %s="%s"', $attr, Convert::raw2att($val));
            }

            $columns->push(ArrayData::create(array(
                'Content'    => $content,
                'Attributes' => DBField::create_field('HTMLFragment', $attrs),
                'IsActions'  => $column == 'Actions'
            )));
        }

        return $columns->renderWith('Bigfork\\SilverstripeUserFormsTidying\\Forms\\GridFieldAddNewFormFieldInlineRow');
    }

    public function handleSave(GridField $grid, DataObjectInterface $record)
    {
        $list  = $grid->getList();
        $value = $grid->Value();

        if (!isset($value[self::POST_KEY]) || !is_array($value[self::POST_KEY])) {
            return;
        }

        $class = $grid->getModelClass();
        /** @var GridFieldEditableColumns $editable */
        $editable = $grid->getConfig()->getComponentByType(GridFieldEditableColumns::class);
        /** @var GridFieldOrderableRows $sortable */
        $sortable = $grid->getConfig()->getComponentByType(GridFieldOrderableRows::class);

        if (!singleton($class)->canCreate()) {
            return;
        }

        foreach ($value[self::POST_KEY] as $fields) {
            $className = $fields['ClassName'] ?? $class;
            if (!class_exists($className) || ($className !== $class && !is_subclass_of($className, $class))) {
                throw new LogicException('Passed an invalid class name');
            }

            /** @var DataObject $item */
            $item  = $className::create();

            // Add the item before the form is loaded so that the join-object is available
            if ($list instanceof ManyManyThroughList) {
                $list->add($item);
            }

            $extra = array();

            $form = $editable->getForm($grid, $item);
            $form->loadDataFrom($fields, Form::MERGE_CLEAR_MISSING);
            $form->saveInto($item);

            // Check if we are also sorting these records
            if ($sortable) {
                $sortField = $sortable->getSortField();
                $item->setField($sortField, $fields[$sortField]);
            }

            if ($list instanceof ManyManyList) {
                $extra = array_intersect_key($form->getData() ?? [], (array) $list->getExtraFields());
            }

            $item->write(false, false, false, true);

            // Add non-through lists after the write. many_many_extraFields are added there too
            if (!($list instanceof ManyManyThroughList)) {
                $list->add($item, $extra);
            }
        }
    }
}
