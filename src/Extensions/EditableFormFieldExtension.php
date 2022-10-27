<?php

namespace Bigfork\SilverstripeUserFormsTidying\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ToggleCompositeField;

class EditableFormFieldExtension extends Extension
{
    private static $db = [
        'Description' => 'Varchar(255)'
    ];

    public function updateCMSFields(FieldList $fields): void
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
        if (!$this->owner->config()->get('literal')) {
            $fields->insertAfter(
                'Title',
                TextField::create('Description', 'Description')
                    ->setDescription('Text to help guide the user on how to fill out the field')
            );
        }

        // Add hints for CMS users about existing fields
        if ($defaultValue = $fields->dataFieldByName('Default')) {
            $defaultValue->setDescription('Value to pre-populate the field with');
        }
        if ($placeholder = $fields->dataFieldByName('Placeholder')) {
            $placeholder->setDescription('Shows the user an example value');
        }

        // Move validation settings to main tab
        /** @var Tab $validationTab */
        $validationTab = $fields->fieldByName('Root.Validation');
        if (!$this->owner->config()->get('literal') && $validationTab) {
            $fields->removeByName(['Validation']);
            foreach ($validationTab->getChildren()->reverse() as $field) {
                $fields->insertAfter('Description', $field);
            }

            $requiredField = $fields->dataFieldByName('Required');
            if ($requiredField) {
                $requiredField->setTitle('Make this a required field');
                $fields->replaceField(
                    'Required',
                    FieldGroup::create('Required field', $requiredField)
                        ->setName('Required')
                );
            }

            $customErrorMessage = $fields->dataFieldByName('CustomErrorMessage');
            if ($customErrorMessage) {
                $customErrorMessage->setTitle('Custom error message');
                $customErrorMessage->setDescription('The error message shown when the user doesn’t complete this field');
                $customErrorMessage->displayIf('Required')->isChecked();
            }
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
                $advancedFields->push($field);
            }
        }
    }

    public function beforeUpdateFormField(FormField $field)
    {
        $field->setDescription($this->owner->Description);
    }
}
