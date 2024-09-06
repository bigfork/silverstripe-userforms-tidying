<?php

namespace Bigfork\SilverstripeUserFormsTidying\Forms;

use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;

class GridFieldDetailForm_ItemRequest_NoPreview extends GridFieldDetailForm_ItemRequest
{
    private static array $allowed_actions = [
        'ItemEditForm',
    ];

    public function ItemEditForm(): Form
    {
        $form = parent::ItemEditForm();
        $form->Fields()->removeByName(['SilverStripeNavigator']);
        $form->removeExtraClass('cms-previewable');
        return $form;
    }
}
