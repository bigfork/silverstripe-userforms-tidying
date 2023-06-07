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
                CheckboxField::create('HideFromReports', _t(EditableLiteralFieldExtension::class.'.HIDEFROMREPORTS_DESCRIPTION', 'Stop this field showing in stored submissions'))
            )->setTitle(_t(EditableLiteralFieldExtension::class.'.HIDEFROMREPORTS_LABEL', 'Hide from reports?'))
        );
    }
}
