<?php

namespace Bigfork\SilverstripeUserFormsTidying;

use Exception;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\HiddenField;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataObjectInterface;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\ORM\ManyManyThroughList;
use SilverStripe\UserForms\Model\EditableFormField\EditableFieldGroup;
use SilverStripe\UserForms\Model\EditableFormField\EditableFieldGroupEnd;
use SilverStripe\View\ArrayData;
use SilverStripe\View\Requirements;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use Symbiote\GridFieldExtensions\GridFieldExtensions;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * Much of this class is copied from GridFieldAddNewInlineButton. It accomplishes two things:
 *
 * 1. When a record is added "inline", two rows are added instead of one - one for EditableFieldGroup
 *    and one for EditableFieldGroupEnd
 * 2. When the records are saved, they're manually set to be instances of EditableFieldGroup and
 *    EditableFieldGroupEnd, as the class name isn't passed in the POST data
 */
class GridFieldAddNewFieldGroupInlineButton extends GridFieldAddNewInlineButton
{
    const POST_KEY = 'GridFieldAddNewFieldGroupInlineButton';

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
        $rows = ArrayList::create();
        $handled = array_keys($editable->getDisplayFields($grid) ?? []);

        foreach ([EditableFieldGroup::class, EditableFieldGroupEnd::class] as $i => $className) {
            $columns = ArrayList::create();
            $record = Injector::inst()->create($className);
            $fields = $editable->getFields($grid, $record);

            foreach ($grid->getColumns() as $column) {
                $field = in_array($column, $handled ?? []) ? $fields->dataFieldByName($column) : null;

                // EditableFieldGroupEnd returns nothing for this -
                if ($column === 'Title' && !$field) {
                    $field = HiddenField::create('Title');
                }

                if ($field) {
                    $field->setName(sprintf(
                        "%s[%s][{$i}{%%=o.num%%}][%s]",
                        $grid->getName(),
                        self::POST_KEY,
                        $field->getName()
                    ));

                    if ($record && $record->hasField($column)) {
                        $value = $record->getField($column);
                        $field->setValue($value);
                    }
                    $content = $field->Field();
                } else {
                    $content = $grid->getColumnContent($record, $column);

                    // Convert GridFieldEditableColumns to the template format
                    $content = str_replace(
                        sprintf('[%s][0]', GridFieldEditableColumns::POST_KEY),
                        sprintf("[%s][{$i}{%%=o.num%%}]", self::POST_KEY),
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

            $rows->push(ArrayData::create([
                'Columns' => $columns
            ]));
        }

        return $rows->renderWith('Bigfork\\SilverstripeUserFormsTidying\\GridFieldAddNewFieldGroupInlineRow');
    }

    public function handleSave(GridField $grid, DataObjectInterface $record)
    {
        $list  = $grid->getList();
        $value = $grid->Value();

        if (!isset($value[self::POST_KEY]) || !is_array($value[self::POST_KEY])) {
            return;
        }

        /** @var GridFieldEditableColumns $editable */
        $editable = $grid->getConfig()->getComponentByType(GridFieldEditableColumns::class);
        /** @var GridFieldOrderableRows $sortable */
        $sortable = $grid->getConfig()->getComponentByType(GridFieldOrderableRows::class);

        if (!singleton(EditableFieldGroup::class)->canCreate()) {
            return;
        }

        $i = 0;
        foreach ($value[self::POST_KEY] as $key => $fields) {
            if ($i > 2) {
                continue;
            }

            // EditableFieldGroup comes through as 01, EditableFieldGroupEnd comes through as 11
            $class = (int)$key < 10 ? EditableFieldGroup::class : EditableFieldGroupEnd::class;

            /** @var DataObject $item */
            $item  = Injector::inst()->create($class);

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

            $i++;
        }
    }
}
