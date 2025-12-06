# Contributing to CI4 Settings

Thank you for considering contributing to CI4 Settings! This document outlines the process for contributing to this project.

## Getting Started

1. Fork the repository
2. Clone your fork locally
3. Create a new branch for your feature or bugfix
4. Make your changes
5. Test your changes
6. Submit a pull request

## Development Setup

```bash
# Clone your fork
git clone https://github.com/YOUR-USERNAME/ci4-settings.git
cd ci4-settings

# Install dependencies
composer install
```

## Coding Standards

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards
- Write clear, descriptive commit messages
- Add comments for complex logic
- Keep methods focused and single-purpose

## Testing

Before submitting a pull request, ensure that:

1. All existing tests pass
2. You've added tests for new features
3. Code coverage remains high

```bash
# Run tests
composer test

# Run with coverage
composer test:coverage
```

## Pull Request Process

1. Update the README.md with details of changes if applicable
2. Update the CHANGELOG.md with notes on your changes
3. Ensure all tests pass
4. Request a review from maintainers
5. Once approved, your PR will be merged

## Types of Contributions

### Bug Reports

- Use the GitHub issue tracker
- Include a clear title and description
- Provide steps to reproduce
- Include error messages if applicable
- Mention your PHP and CodeIgniter version

### Feature Requests

- Use the GitHub issue tracker
- Clearly describe the feature
- Explain why it would be useful
- Provide examples if possible

### Code Contributions

- Bug fixes
- New features
- Documentation improvements
- Performance improvements

## Code Review

All submissions require review. We use GitHub pull requests for this purpose. Reviewers will:

- Check code quality
- Verify tests pass
- Ensure documentation is updated
- Confirm the change aligns with project goals

## Questions?

Feel free to open an issue for any questions about contributing!
