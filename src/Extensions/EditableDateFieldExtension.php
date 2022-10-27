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
                CheckboxField::create('DefaultToToday', 'Pre-populate the field with today’s date')
            )
        );
    }
}
