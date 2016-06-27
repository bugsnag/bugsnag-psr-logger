Contributing
============

-   [Fork](https://help.github.com/articles/fork-a-repo) the [notifier on github](https://github.com/bugsnag/bugsnag-laravel)
-   Build and test your changes. Run the tests using [phpunit](https://phpunit.de) (vendored to `vendor/bin/phpunit`)
-   Commit and push until you are happy with your contribution
-   [Make a pull request](https://help.github.com/articles/using-pull-requests)
-   Thanks!

Releasing
=========

1. Commit all outstanding changes
2. Update the CHANGELOG.md, and README if appropriate.
3. Commit, tag push
    ```
    git commit -am v1.x.x
    git tag v1.x.x
    git push origin master && git push --tags
    ```
4. Update the setup guides for PHP (and its frameworks) with any new content.
