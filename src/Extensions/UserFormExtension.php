<?php

namespace Bigfork\SilverstripeUserFormsTidying\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\UserForms\Form\UserForm;

class UserFormExtension extends Extension
{
    public function updateForm(): void
    {
        /** @var UserForm $form */
        $form = $this->owner;
        $form->setAttribute('novalidate', true);
    }

    public function updateFormActions(FieldList $actions): void
    {
        /** @var FormAction $action */
        foreach ($actions as $action) {
            $action->setUseButtonTag(true)
                ->addExtraClass('button');
        }
    }
}
