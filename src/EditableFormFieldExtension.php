<?php

namespace Bigfork\SilverstripeUserFormsTidying;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ToggleCompositeField;

class EditableFormFieldExtension extends Extension
{
    private static $db = [
        'Description' => 'Varchar(255)'
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName(['RightTitle']);

        $fields->insertAfter(
            'Title',
            TextField::create('Description', 'Description')
                ->setDescription('Text to help guide the user on how to fill out the field')
        );

        $fields->dataFieldByName('Default')
                ->setDescription('Value to pre-populate the field with');
        $fields->dataFieldByName('Placeholder')
            ->setDescription('Shows the user an example value');

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
}
