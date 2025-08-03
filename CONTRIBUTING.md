# Contributing to Laravel Textify

Thank you for considering contributing to Laravel Textify! We welcome contributions from the community.

## Code of Conduct

Please note that this project is released with a Contributor Code of Conduct. By participating in this project you agree to abide by its terms.

## How to Contribute

### Reporting Bugs

Before creating bug reports, please check the existing issues as you might find out that you don't need to create one. When you are creating a bug report, please include as many details as possible:

-   Use a clear and descriptive title
-   Describe the exact steps which reproduce the problem
-   Provide specific examples to demonstrate the steps
-   Describe the behavior you observed after following the steps
-   Explain which behavior you expected to see instead and why
-   Include details about your configuration and environment

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion, please include:

-   Use a clear and descriptive title
-   Provide a step-by-step description of the suggested enhancement
-   Provide specific examples to demonstrate the steps
-   Describe the current behavior and explain which behavior you expected to see instead
-   Explain why this enhancement would be useful

### Development Process

1. Fork the repository
2. Create a new branch for your feature (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Add or update tests as needed
5. Ensure all tests pass (`composer test`)
6. Run code quality checks (`composer analyse` and `composer format`)
7. Commit your changes (`git commit -m 'Add some amazing feature'`)
8. Push to the branch (`git push origin feature/amazing-feature`)
9. Open a Pull Request

### Pull Request Guidelines

-   Fill in the required template
-   Do not include issue numbers in the PR title
-   Include screenshots and animated GIFs in your pull request whenever possible
-   Follow the PHP coding standards (Laravel Pint will format your code)
-   Include thoughtfully-worded, well-structured tests
-   Document new code based on the Documentation Styleguide
-   End all files with a newline

### Development Setup

```bash
# Clone the repository
git clone https://github.com/devwizardhq/laravel-textify.git
cd laravel-textify

# Install dependencies
composer install

# Run tests
composer test

# Run code analysis
composer analyse

# Format code
composer format
```

### Adding New SMS Providers

When adding a new SMS provider:

1. Create a new provider class extending `BaseProvider`
2. Implement the required methods (`send`, `getName`, etc.)
3. Add configuration options to the config file
4. Add tests for the new provider
5. Update documentation

Example provider structure:

```php
<?php

namespace DevWizard\Textify\Providers\YourCategory;

use DevWizard\Textify\Providers\BaseProvider;
use DevWizard\Textify\DTOs\TextifyMessage;
use DevWizard\Textify\DTOs\TextifyResponse;

class YourProvider extends BaseProvider
{
    public function send(TextifyMessage $message): TextifyResponse
    {
        // Implementation
    }

    public function getName(): string
    {
        return 'your-provider';
    }
}
```

### Testing

-   Write tests for new features
-   Ensure all tests pass
-   Maintain or improve code coverage
-   Use descriptive test names
-   Test both success and failure scenarios

### Documentation

-   Update the README.md if you add new features
-   Add DocBlocks to all public methods
-   Follow Laravel documentation standards
-   Update the CHANGELOG.md

### Code Style

We follow Laravel coding standards. Use Laravel Pint to format your code:

```bash
composer format
```

### Commit Messages

Use clear and meaningful commit messages. Follow the convention:

-   `feat: add new SMS provider`
-   `fix: resolve queue processing issue`
-   `docs: update README with new examples`
-   `test: add tests for fallback system`
-   `refactor: improve provider loading`

## Recognition

Contributors will be recognized in the README.md file and in release notes.

## License

By contributing to Laravel Textify, you agree that your contributions will be licensed under its MIT license.
