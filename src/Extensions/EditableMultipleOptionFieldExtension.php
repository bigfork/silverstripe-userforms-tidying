<?php

namespace Bigfork\SilverstripeUserFormsTidying\Extensions;

use Bigfork\SilverstripeUserFormsTidying\Forms\CheckboxFieldWithExtraPadding;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TextField;
use SilverStripe\UserForms\Model\EditableFormField\EditableCheckboxGroupField;
use SilverStripe\UserForms\Model\EditableFormField\EditableDropdown;
use SilverStripe\UserForms\Model\EditableFormField\EditableMultipleOptionField;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;

class EditableMultipleOptionFieldExtension extends Extension
{
    public function updateCMSFields(FieldList $fields): void
    {
        if ($this->owner instanceof EditableCheckboxGroupField) {
            $fields->removeByName(['Default']);
        }

        /** @var Tab $optionsTab */
        $optionsTab = $fields->fieldByName('Root.Options');
        if (!$optionsTab) {
            return;
        }

        // Move options settings to main tab
        $fields->removeByName(['Options']);
        foreach ($optionsTab->getChildren() as $field) {
            $fields->addFieldToTab('Root.Main', $field, 'Advanced');
        }

        /** @var GridField $optionsGridField */
        $optionsGridField = $fields->dataFieldByName('Options');

        /** @var GridFieldAddNewInlineButton $addNewButton */
        $addNewButton = $optionsGridField->getConfig()->getComponentByType(GridFieldAddNewInlineButton::class);
        $addNewButton->setTitle(_t(__CLASS__.'.ADD_OPTION', 'Add new option'));

        /** @var GridFieldEditableColumns $editableColumns */
        $editableColumns = $optionsGridField->getConfig()->getComponentByType(GridFieldEditableColumns::class);
        // Use CheckboxFieldWithExtraPadding because the default CheckboxField layout sucks when inline editing
        $editableColumns->setDisplayFields([
            'Title' => [
                'title' => _t(EditableMultipleOptionField::class.'.TITLE', 'Title'),
                'field' => TextField::class
            ],
            'Value' => [
                'title' => _t(EditableMultipleOptionField::class.'.VALUE', 'Value'),
                'field' => TextField::class
            ],
            'Default' => [
                'title' => _t(EditableMultipleOptionField::class.'.DEFAULT', 'Selected by default?'),
                'field' => CheckboxFieldWithExtraPadding::class
            ]
        ]);

        // Move empty string settings after the GridField
        if ($this->owner instanceof EditableDropdown) {
            $fields->removeByName(['UseEmptyString', 'EmptyString']);

            $fields->addFieldsToTab(
                'Root.Main',
                [
                    FieldGroup::create(
                        'Empty value',
                        CheckboxField::create('UseEmptyString', _t(__CLASS__.'.EMPTYSTRING_CHECKBOX_DESCRIPTION', 'Display custom text when no value has been selected'))
                    )->setTitle(_t(__CLASS__.'.EMPTYSTRING_CHECKBOX_LABEL', 'Empty value')),
                    $emptyString = TextField::create('EmptyString', _t(__CLASS__.'.EMPTYSTRING_LABEL', 'Empty value text'))
                        ->setDescription(_t(__CLASS__.'.EMPTYSTRING_DESCRIPTION', 'Shown when no value has been selected'))
                ],
                'Advanced'
            );

            $emptyString->displayIf('UseEmptyString')->isChecked();
        }
    }
}
