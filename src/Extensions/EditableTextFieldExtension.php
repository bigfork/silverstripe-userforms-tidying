<?php

namespace Bigfork\SilverstripeUserFormsTidying\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;

class EditableTextFieldExtension extends Extension
{
    public function updateCMSFields(FieldList $fields): void
    {
        /** @var FieldGroup $length */
        $length = $fields->fieldByName('Root.Main.Allowedtextlength');
        if (!$length) {
            return;
        }

        // Move text length validation to just before 'advanced'
        $fields->removeByName(['Allowedtextlength']);
        $fields->insertBefore('Advanced', $length);
    }
}
