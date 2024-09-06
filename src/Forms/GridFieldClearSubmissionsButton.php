<?php

namespace Bigfork\SilverstripeUserFormsTidying\Forms;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\GridField\AbstractGridFieldComponent;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Forms\GridField\GridField_URLHandler;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\View\Requirements;

class GridFieldClearSubmissionsButton extends AbstractGridFieldComponent implements GridField_HTMLProvider, GridField_ActionProvider, GridField_URLHandler
{
    protected string $targetFragment;

    public function __construct(string $targetFragment = 'after')
    {
        $this->targetFragment = $targetFragment;
    }

    public function getHTMLFragments($gridField): array
    {
        $button = new GridField_FormAction(
            $gridField,
            'clear_submissions',
            'Clear all submissions',
            'clear_submissions',
            []
        );
        $button->addExtraClass('btn btn-secondary font-icon-trash grid-field__clear-submissions');
        $button->setForm($gridField->getForm());
        return [
            $this->targetFragment => $button->Field(),
        ];
    }

    public function getActions($gridField): array
    {
        return ['clear_submissions'];
    }

    public function handleAction(GridField $gridField, $actionName, $arguments, $data): void
    {
        if ($actionName == 'clear_submissions') {
            $this->handleClear($gridField);
        }
    }

    public function getURLHandlers($gridField): array
    {
        return [
            'clear_submissions' => 'handleClear',
        ];
    }

    public function handleClear(GridField $gridField, HTTPRequest $request = null): void
    {
        $config = $gridField->getConfig();
        $paginator = $config->getComponentByType(GridFieldPaginator::class);
        if ($paginator) {
            $config->removeComponent($paginator);
        }

        $items = $gridField->getManipulatedList();
        $items = $items->limit(null);

        foreach ($items as $item) {
            $item->delete();
        }

        if ($paginator) {
            $config->addComponent($paginator);
        }

        $gridField->setMessage('Submissions cleared successfully', ValidationResult::TYPE_GOOD);
    }
}
