<?php

namespace Bigfork\SilverstripeUserFormsTidying\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;

class EditableFormHeadingExtension extends Extension
{
    public function updateCMSFields(FieldList $fields): void
    {
        $fields->replaceField(
            'HideFromReports',
            FieldGroup::create(
                'Hide from reports?',
                CheckboxField::create('HideFromReports', 'Stop this field showing in stored submissions')
            )
        );
    }
}
