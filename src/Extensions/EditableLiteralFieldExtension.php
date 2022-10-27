<?php

namespace Bigfork\SilverstripeUserFormsTidying\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;

class EditableLiteralFieldExtension extends Extension
{
    public function updateCMSFields(FieldList $fields): void
    {
        // We always hide the label on a literal field
        $fields->removeByName(['HideLabel']);

        $fields->replaceField(
            'HideFromReports',
            FieldGroup::create(
                'Hide from reports?',
                CheckboxField::create('HideFromReports', 'Stop this field showing in stored submissions')
            )
        );
    }
}
