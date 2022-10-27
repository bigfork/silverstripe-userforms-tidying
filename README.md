# Silverstripe UserForms Tidying

Opinionated changes to the userforms module by Bigfork.

```bash
composer install bigfork/silverstripe-userforms-tidying
```

## Changes

Changes include, but aren’t limited to:

- Forces userforms via an elemental block - `UserDefinedForm` pages can't be created in the CMS
- Removes a few fields that confuse content authors
- Moves/replaces/amends other fields to make them more user-friendly
- Makes the Add Field/Add Page/Add Field Group functionality work with add-new-inline to prevent triggering a reload when adding to the form
- Adds "required" and "show in summary" as inline-editable fields
- Moves content fields for the userform block into the "configuration" tab, as they're edited less often
- Removes history tab from the userform block, as it's effectively useless
- Makes submissions easier to search by adding min/max date filters
- Includes a set of templates with BEM(ish) CSS classes
