<?php

namespace Bigfork\SilverstripeUserFormsTidying\Forms;

use SilverStripe\Forms\GridField\GridField_ActionMenuLink;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldViewButton;

class GridFieldEditButtonRenderedAsViewButton extends GridFieldEditButton implements GridField_ColumnProvider, GridField_ActionProvider, GridField_ActionMenuLink
{
    protected $extraClass = [
        'grid-field__icon-action--hidden-on-hover' => true,
        'font-icon-eye' => true,
        'btn--icon-large' => true,
        'action-menu--handled' => true
    ];

    public function getTitle($gridField, $record, $columnName): string
    {
        return _t(GridFieldViewButton::class . '.VIEW', 'View');
    }

    public function getExtraData($gridField, $record, $columnName): array
    {
        return [
            "classNames" => "font-icon-eye action-detail edit-link"
        ];
    }
}
