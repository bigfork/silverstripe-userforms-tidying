<?php

namespace Bigfork\SilverstripeUserFormsTidying\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;

class EditableDateFieldExtension extends Extension
{
    public function updateCMSFields(FieldList $fields): void
    {
        $fields->replaceField(
            'DefaultToToday',
            FieldGroup::create(
                'Default to today',
                CheckboxField::create('DefaultToToday', _t(__CLASS__.'.PREPOPULATE', 'Pre-populate the field with today’s date'))
            )->setTitle(_t(__CLASS__.'.DEFAULTTOTODAY', 'Default to today'))
        );
    }
}
