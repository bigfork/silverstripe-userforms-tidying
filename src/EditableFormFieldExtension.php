<?php

namespace Bigfork\SilverstripeUserFormsTidying;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ToggleCompositeField;

class EditableFormFieldExtension extends Extension
{
    private static $db = [
        'Description' => 'Varchar(255)'
    ];

    public function updateCMSFields(FieldList $fields)
    {
        // Make "show in summary" field clearer for CMS users
        $fields->replaceField(
            'ShowInSummary',
            FieldGroup::create(
                'Summary field',
                CheckboxField::create('ShowInSummary', 'Show this field in the “summary” column for submissions')
            )
        );

        // Remove right title - description makes this redundant
        $fields->removeByName(['RightTitle']);

        // Add a description option
        $fields->insertAfter(
            'Title',
            TextField::create('Description', 'Description')
                ->setDescription('Text to help guide the user on how to fill out the field')
        );

        // Add hints for CMS users about existing fields
        if ($defaultValue = $fields->dataFieldByName('Default')) {
            $defaultValue->setDescription('Value to pre-populate the field with');
        }
        if ($placeholder = $fields->dataFieldByName('Placeholder')) {
            $placeholder->setDescription('Shows the user an example value');
        }

        // Move fields CMS users will rarely need to an "Advanced" toggle section
        $fields->addFieldToTab(
            'Root.Main',
            $advancedFields = ToggleCompositeField::create('Advanced', 'Advanced', [])
        );
        $advancedFieldNames = [
            'Name',
            'MergeField',
            'ExtraClass',
        ];
        foreach ($advancedFieldNames as $advancedFieldName) {
            $field = $fields->flattenFields()->fieldByName($advancedFieldName);
            $fields->removeByName($advancedFieldName);

            if ($field) {
                $advancedFields->getChildren()->push($field);
            }
        }
    }

    public function beforeUpdateFormField(FormField $field)
    {
        $field->setDescription($this->owner->Description);
    }
}
