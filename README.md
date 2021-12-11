# OS2Web Citizen Proposals module

## Module purpose

The aim of to add Citizen proposals types and view.

## Additional settings
Settings are available under ```admin/config/content/os2web-citizen-proposals```
* **Admin email** - email to receive now proposal created notifications
* **Maximum votes** - Number of votes needed for proposal
* **Unpublish proposal older than** - Any proposals older that this period will be unpublished
* **Delete proposal older than** - Any proposals older that this period will be deleted
* **Email template** - Email templates settings

## Install

Module is available to download via composer.
```
composer require os2web/os2web_citizen_proposals
drush en os2web_citizen_proposals
```

## Update
Updating process for OS2Web Citizen Proposal module is similar to usual Drupal 8 module.
Use Composer's built-in command for listing packages that have updates available:

```
composer outdated os2web/os2web_citizen_proposals
```

## Automated testing and code quality
See [OS2Web testing and CI information](https://github.com/OS2Web/docs#testing-and-ci)

## Contribution

Project is opened for new features and os course bugfixes.
If you have any suggestion or you found a bug in project, you are very welcome
to create an issue in github repository issue tracker.
For issue description there is expected that you will provide clear and
sufficient information about your feature request or bug report.

### Code review policy
See [OS2Web code review policy](https://github.com/OS2Web/docs#code-review)

### Git name convention
See [OS2Web git name convention](https://github.com/OS2Web/docs#git-guideline)
