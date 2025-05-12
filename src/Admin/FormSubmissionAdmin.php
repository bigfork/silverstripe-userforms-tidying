<?php

namespace Bigfork\SilverstripeUserFormsTidying\Admin;

use Bigfork\SilverstripeUserFormsTidying\Forms\GridFieldClearSubmissionsButton;
use Bigfork\SilverstripeUserFormsTidying\Forms\GridFieldDetailForm_ItemRequest_NoPreview;
use Bigfork\SilverstripeUserFormsTidying\Forms\GridFieldEditButtonRenderedAsViewButton;
use DNADesign\ElementalUserForms\Model\ElementForm;
use SilverStripe\Admin\LeftAndMain;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\ORM\SS_List;
use SilverStripe\UserForms\Model\Submission\SubmittedForm;
use SilverStripe\Versioned\GridFieldArchiveAction;

class FormSubmissionsAdmin extends LeftAndMain
{
    private static string $url_segment = 'form-submissions';

    private static string $url_rule = '/$Action/$ID/$OtherID';

    private static string $menu_title = 'Form Submissions';

    private static float $menu_priority = -0.5;

    private static string $menu_icon_class = 'font-icon-book-open';

    protected function getFormList(): SS_List
    {
        return ElementForm::get()->setDataQueryParam('Versioned.mode', 'latest_versions')
            ->sort('Title ASC')
            ->filterByCallback(function (ElementForm $element) {
                // Only include archived elements if they have at least one submission
                return !$element->isArchived() && $element->Submissions()->count() > 0;
            })
            ->filterByCallback(function (ElementForm $element) {
                return $element->canEdit();
            });
    }

    public function getEditForm($id = null, $fields = null): ?Form
    {
        $fields = FieldList::create(
            TabSet::create(
                'Root',
                Tab::create(
                    'EditableForms',
                    'Editable forms',
                    GridField::create(
                        'EditableForms',
                        'Editable forms',
                        $this->getFormList(),
                        // GridField actions in the nested form get removed with GridFieldConfig_RecordViewer, so we
                        // have to start with GridFieldConfig_RecordEditor and remove bits we don't want
                        $config = GridFieldConfig_RecordEditor::create()
                            ->removeComponentsByType([
                                GridFieldAddNewButton::class,
                                GridFieldDeleteAction::class,
                                GridFieldArchiveAction::class,
                            ])
                    )->setModelClass(SubmittedForm::class)
                )
            )
        );

        // Replace "edit" button with one that's labelled "view"
        $editButton = $config->getComponentByType(GridFieldEditButton::class);
        $config->addComponent(new GridFieldEditButtonRenderedAsViewButton(), GridFieldEditButton::class);
        $config->removeComponent($editButton);

        /** @var GridFieldDataColumns $columns */
        $columns = $config->getComponentByType(GridFieldDataColumns::class);
        $columns->setDisplayFields([
            'Title' => 'Title',
            'PageBreadcrumbs' => 'Page',
            'NumberOfSubmissions' => 'Number of submissions',
        ]);
        $columns->setFieldFormatting([
            'Title' => function ($field, ElementForm $record) {
                return $record->Title ?: 'Untitled form';
            },
            'PageBreadcrumbs' => function ($field, ElementForm $record) {
                $page = $record->getPage();
                if (!$page instanceof SiteTree) {
                    return 'Unknown page';
                }
                return $page->Breadcrumbs(20, true, false, true);
            },
            'NumberOfSubmissions' => function ($field, ElementForm $record) {
                return $record->Submissions()->count();
            },
        ]);

        /** @var GridFieldDetailForm $detailForm */
        $detailForm = $config->getComponentByType(GridFieldDetailForm::class);
        $detailForm->setItemEditFormCallback(function (Form $form) {
            /** @var ElementForm $record */
            $record = $form->getRecord();

            $info = null;
            $page = $record->getPage();
            if (!$record->isArchived() && $page instanceof SiteTree) {
                $info = <<<HTML
<div class="alert alert-info">
<a href="{$page->AbsoluteLink()}" class="btn btn-primary font-icon-search" target="_blank">View page</a>
<a href="{$page->CMSEditLink()}" class="btn btn-primary font-icon-edit" target="_blank">Edit page</a>
<a href="{$record->getEditLink()}" class="btn btn-primary font-icon-edit" target="_blank">Edit form</a>
</div>
HTML;
            }

            $form->setFields(FieldList::create(
                TabSet::create(
                    'Root',
                    Tab::create(
                        'Main',
                        LiteralField::create('Info', $info),
                        $gridfield = $record->getSubmissionsGridField()
                    )
                )
            ));

            $gridfield->setTitle('Submissions');
            $gridfield->getConfig()->addComponent(new GridFieldClearSubmissionsButton('buttons-after-right'));

            $form->setActions(FieldList::create());
        });

        $detailForm->setItemRequestClass(GridFieldDetailForm_ItemRequest_NoPreview::class);

        $form = Form::create(
            $this,
            'EditForm',
            $fields,
        )->setHTMLID('Form_EditForm');

        $form->addExtraClass('cms-edit-form');
        $form->setTemplate($this->getTemplatesWithSuffix('_EditForm'));
        $form->addExtraClass('flexbox-area-grow fill-height cms-content cms-edit-form');

        if ($form->Fields()->hasTabSet()) {
            $form->Fields()->findOrMakeTab('Root')->setTemplate('SilverStripe\\Forms\\CMSTabSet');
        }

        return $form;
    }
}
