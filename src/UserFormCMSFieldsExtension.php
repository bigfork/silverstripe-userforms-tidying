<?php

namespace Bigfork\SilverstripeUserFormsTidying;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldFilterHeader;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\Tab;
use SilverStripe\ORM\Filters\GreaterThanOrEqualFilter;
use SilverStripe\ORM\Filters\LessThanOrEqualFilter;
use SilverStripe\ORM\Search\SearchContext;
use SilverStripe\UserForms\Form\GridFieldAddClassesButton;
use SilverStripe\UserForms\Form\UserFormsGridFieldFilterHeader;
use SilverStripe\UserForms\Model\EditableFormField;
use SilverStripe\UserForms\Model\EditableFormField\EditableFieldGroup;
use SilverStripe\UserForms\Model\EditableFormField\EditableFieldGroupEnd;
use SilverStripe\UserForms\Model\EditableFormField\EditableFileField;
use SilverStripe\UserForms\Model\EditableFormField\EditableFormStep;
use SilverStripe\UserForms\Model\EditableFormField\EditableTextField;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use Symbiote\GridFieldExtensions\GridFieldTitleHeader;

class UserFormCMSFieldsExtension extends Extension
{
    public function updateCMSFields(FieldList $fields): void
    {
        $this->tidyFieldEditorGridField($fields);
        $this->tidyConfigurationFields($fields);
        $this->removeUserUnfriendlyFields($fields);
        $this->reorderTabs($fields);
        $this->mergeRecipientsTabIntoConfiguration($fields);
        $this->updateSubmissionsGridField($fields);
    }

    protected function tidyFieldEditorGridField(FieldList $fields): void
    {
        /** @var GridField $fieldsGridField */
        $fieldsGridField = $fields->dataFieldByName('Fields');
        if (!$fieldsGridField) {
            return;
        }

        $fieldsGridField->getConfig()->addComponent(GridFieldTitleHeader::create());

        /** @var GridFieldEditableColumns $editableColumns */
        $editableColumns = $fieldsGridField->getConfig()->getComponentByType(GridFieldEditableColumns::class);
        $fieldClasses = singleton(EditableFormField::class)->getEditableFieldClasses();
        $editableColumns->setDisplayFields([
            'ClassName' => [
                'title' => 'Field type',
                'callback' => function (EditableFormField $record, $column) use ($fieldClasses) {
                    $field = $record->getInlineClassnameField($column, $fieldClasses);
                    $field->setValue(EditableTextField::class);

                    if ($record instanceof EditableFileField) {
                        $field->setAttribute('data-folderconfirmed', $record->FolderConfirmed ? 1 : 0);
                    }

                    return $field;
                }
            ],
            'Title' => [
                'title' => 'Title',
                'callback' => function (EditableFormField $record, $column) {
                    return $record->getInlineTitleField($column);
                }
            ],
            'Required' => [
                'title' => 'Required',
                'callback' => function (EditableFormField $record, $column) {
                    if ($record instanceof EditableFormStep || $record instanceof EditableFieldGroup || $record instanceof EditableFieldGroupEnd) {
                        return HiddenField::create($column);
                    }

                    return CheckboxFieldWithExtraPadding::create($column);
                }
            ],
            'ShowInSummary' => [
                'title' => 'Show in summary?',
                'callback' => function (EditableFormField $record, $column) {
                    if ($record instanceof EditableFormStep || $record instanceof EditableFieldGroup || $record instanceof EditableFieldGroupEnd) {
                        return HiddenField::create($column);
                    }

                    return CheckboxFieldWithExtraPadding::create($column);
                }
            ]
        ]);

        $fieldsGridField->getConfig()->removeComponentsByType(GridFieldAddClassesButton::class);

        $fieldsGridField->getConfig()->addComponent(
            (GridFieldAddNewFormFieldInlineButton::create())
                ->setTitle('Add form field')
        );
        $fieldsGridField->getConfig()->addComponent(
            (GridFieldAddNewPageBreakInlineButton::create())
                ->setTitle('Add page break')
        );
        $fieldsGridField->getConfig()->addComponent(
            (GridFieldAddNewFieldGroupInlineButton::create())
                ->setTitle('Add field group')
        );
    }

    protected function tidyConfigurationFields(FieldList $fields): void
    {
        /** @var Tab $configuration */
        $configuration = $fields->fieldByName('Root.FormOptions');
        $configuration->removeByName('OnCompleteMessageLabelOnCompleteMessage');
        $configuration->unshift(
            HTMLEditorField::create('OnCompleteMessage', 'Show on completion')
                ->setRows(3)
                ->addExtraClass('stacked')
        );

        // Add placeholder to submit button text to show default value
        $fields->dataFieldByName('SubmitButtonText')
            ->setTitle('Submit button text')
            ->setAttribute('placeholder', 'Submit');

        // Make "disable save to server" field more user friendly
        $fields->replaceField(
            'DisableSaveSubmissions',
            DropdownField::create('DisableSaveSubmissions', 'Save submissions to CMS?', [
                0 => 'Yes',
                1 => 'No'
            ])
        );
    }

    protected function removeUserUnfriendlyFields(FieldList $fields): void
    {
        $fields->removeByName([
            'ClearButtonText',
            'ShowClearButton',
            'EnableLiveValidation',
            'DisplayErrorMessagesAtTop',
            'DisableCsrfSecurityToken',
            'DisableAuthenicatedFinishAction'
        ]);
    }

    protected function reorderTabs(FieldList $fields): void
    {
        /** @var Tab $configuration */
        $configuration = $fields->fieldByName('Root.FormOptions');
        // Move configuration tab so it comes after form fields
        $fields->removeByName('FormOptions');
        $fields->insertBefore('Submissions', $configuration);
    }

    protected function mergeRecipientsTabIntoConfiguration(FieldList $fields): void
    {
        /** @var Tab $configuration */
        $configuration = $fields->fieldByName('Root.FormOptions');
        /** @var Tab $recipients */
        $recipients = $fields->fieldByName('Root.Recipients');

        // Move "Recipients" tab fields to configuration tab
        foreach ($recipients->getChildren() as $child) {
            $configuration->push($child);
        }

        // Remove "Recipients" tab now that it's empty
        $fields->removeByName('Recipients');
    }

    protected function updateSubmissionsGridField(FieldList $fields): void
    {
        /** @var GridField $submissions */
        $submissions = $fields->dataFieldByName('Submissions');
        if (!$submissions) {
            return;
        }

        $config = $submissions->getConfig();
        /** @var UserFormsGridFieldFilterHeader $filterHeader */
        $filterHeader = $config->getComponentByType(UserFormsGridFieldFilterHeader::class);
        if (!$filterHeader) {
            return;
        }

        // Add filters for min/max date
        $updateSearchContext = function(SearchContext $context) {
            $filters = $context->getFilters();
            $filters['MinDate'] = GreaterThanOrEqualFilter::create('Created');
            $filters['MaxDate'] = LessThanOrEqualFilter::create('Created');
            $context->setFilters($filters);
        };

        // Add date range fields to submission search fields
        $updateSearchForm = function(Form $form) {
            $form->Fields()->removeByName(['Search__Created']);
            $form->Fields()->push($minDate = DateField::create('MinDate', 'Submitted from'));
            $form->Fields()->push($maxDate = DateField::create('MaxDate', 'Submitted to'));

            foreach ([$minDate, $maxDate] as $field) {
                $field->addExtraClass('stacked')
                    ->setForm($form);
            }
        };

        // Update GridField components
        $config->addComponent(new GridFieldButtonRow('before'));
        $config->removeComponent($filterHeader);
        $config->addComponent(new GridFieldFilterHeader(false, $updateSearchContext, $updateSearchForm));

        // Filter only works if paginator is added after it, so we have to remove and re-add it
        // https://github.com/silverstripe/silverstripe-framework/issues/8454
        $config->removeComponentsByType(GridFieldPaginator::class);
        $config->addComponent(new GridFieldPaginator());
    }
}
