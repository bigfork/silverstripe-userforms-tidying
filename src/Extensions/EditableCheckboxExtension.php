<?php

namespace Bigfork\SilverstripeUserFormsTidying\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\UserForms\Model;

class EditableCheckboxExtension extends Extension
{
    public function updateCMSFields(FieldList $fields): void
    {
        $fields->replaceField(
            'CheckedDefault',
            FieldGroup::create(
                'Default value',
                CheckboxField::create('CheckedDefault', _t(EditableFormField::class . '.CHECKEDBYDEFAULT', 'Checked by Default?'))
            )->setTitle(_t(__CLASS__.'.DEFAULT_VALUE', 'Default value'))
        );
    }
}
