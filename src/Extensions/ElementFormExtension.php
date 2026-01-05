<?php

namespace Bigfork\SilverstripeUserFormsTidying\Extensions;

use Bigfork\SilverstripeUserFormsTidying\Forms\GridFieldClearSubmissionsButton;
use DNADesign\Elemental\Forms\TextCheckboxGroupField;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\Tab;
use SilverStripe\SpamProtection\EditableSpamProtectionField;

class ElementFormExtension extends Extension
{
    public function updateCMSFields(FieldList $fields): void
    {
        // Remove "history" tab - it's basically useless for the form block
        $fields->removeByName(['History']);

        /** @var Tab $content */
        $content = $fields->fieldByName('Root.Main');
        // Move form fields from "Content" tab to "Configuration"
        if ($content instanceof Tab) {
            /** @var Tab $configuration */
            $configuration = $fields->fieldByName('Root.FormOptions');
            // Move fields to configuration tab
            foreach ($content->getChildren()->reverse() as $child) {
                $configuration->unshift($child);
            }

            // TextCheckboxGroupField seems to be buggy when moved around, so we completely remove and re-add it
            if ($configuration->fieldByName('Title')) {
                $configuration->removeByName('Title');
                $configuration->unshift(TextCheckboxGroupField::create('Title'));
            }

            // Remove "Content" tab now that it's empty
            $fields->removeByName('Main');
        }

        /** @var GridField $submissions */
        $submissions = $fields->dataFieldByName('Submissions');
        $submissions->getConfig()->addComponent(new GridFieldClearSubmissionsButton('buttons-after-right'));
    }

    /**
     * Workaround for https://github.com/dnadesign/silverstripe-elemental-userforms/issues/41
     */
    public function onBeforeDuplicate()
    {
        $this->owner->write();
    }

    public function updateBlockSchema(array &$schema)
    {
        if (!$this->owner->EmailRecipients()->count()) {
            $schema['content'] = '⚠️ form has no email recipients';
        }
        if (
            class_exists(EditableSpamProtectionField::class) &&
            !$this->owner->Fields()->filter(['ClassName' => EditableSpamProtectionField::class])->count()
        ) {
            $schema['content'] = '⚠️ form has no spam protection';
        }
    }
}
