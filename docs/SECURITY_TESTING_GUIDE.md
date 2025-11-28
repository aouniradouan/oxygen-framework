# OxygenFramework Security & Testing System

## Overview

OxygenFramework now includes a next-generation security and testing system with:

- **Comprehensive Security Scanner**: Detects SQL injection, XSS, CSRF, and dangerous code patterns
- **Virus/Malware Detection**: Scans for malicious code with quarantine capabilities
- **AI-Powered Test Auto-Generation**: Automatically generates comprehensive tests
- **Security Auto-Fix**: Automatically fixes common security vulnerabilities
- **Framework Update System**: Safe, automated framework updates

## Security Commands

### Security Scan

Run a comprehensive security audit on your project:

```bash
# Scan all security issues
php oxygen security:scan

# Scan specific type
php oxygen security:scan --type=sql
php oxygen security:scan --type=xss
php oxygen security:scan --type=csrf

# Scan and auto-fix
php oxygen security:scan --fix
```

**Output**: Generates an HTML report in `storage/reports/security-scan-[timestamp].html`

### Virus Scan

Scan for viruses and malware:

```bash
# Basic scan
php oxygen virus:scan

# Deep scan with quarantine
php oxygen virus:scan --deep --quarantine
```

**Output**: Generates an HTML report in `storage/reports/virus-scan-[timestamp].html`

Quarantined files are moved to `storage/quarantine/` with metadata.

## Testing Commands

### Generate Tests

Auto-generate tests for all components:

```bash
# Generate all tests
php oxygen test:generate

# Generate specific type
php oxygen test:generate --type=unit
php oxygen test:generate --type=integration

# Use AI for intelligent test generation
php oxygen test:generate --ai
```

**Generated Tests**:
- `tests/Unit/Controllers/` - Controller tests
- `tests/Unit/Models/` - Model tests  
- `tests/Unit/Services/` - Service tests
- `tests/Integration/` - Integration tests

### Run Tests

Execute tests with coverage and reporting:

```bash
# Run all tests
php oxygen test:all

# Run with coverage
php oxygen test:all --coverage

# Generate HTML report
php oxygen test:all --report
```

**Output**:
- HTML Report: `storage/reports/test-report.html`
- Coverage Report: `storage/coverage/index.html`

## Framework Update

### Check for Updates

```bash
php oxygen framework:update --check-only
```

### Update Framework

```bash
# Update with backup
php oxygen framework:update --backup

# Update without confirmation
php oxygen framework:update
```

## Configuration

### Security Configuration

Edit `config/security.php`:

```php
return [
    'mode' => 'balanced', // strict, balanced, permissive
    
    'enabled_checks' => [
        'sql_injection' => true,
        'xss' => true,
        'csrf' => true,
        'file_upload' => true,
        'code_patterns' => true,
        'configuration' => true,
    ],
    
    'auto_fix' => [
        'enabled' => true,
        'create_backup' => true,
    ],
];
```

### Testing Configuration

Edit `config/testing.php`:

```php
return [
    'generation' => [
        'ai_enabled' => false,
        'generate_edge_cases' => true,
    ],
    
    'coverage' => [
        'min_threshold' => 70,
    ],
];
```

## Security Scanner Details

### Vulnerability Detection

The security scanner detects:

1. **SQL Injection**
   - Direct SQL concatenation
   - Unparameterized queries
   - mysqli_query with variables

2. **XSS (Cross-Site Scripting)**
   - Unescaped echo statements
   - Direct $_GET/$_POST output
   - innerHTML assignments

3. **CSRF (Cross-Site Request Forgery)**
   - POST forms without CSRF tokens
   - State-changing operations without validation

4. **Dangerous Code Patterns**
   - eval() usage
   - exec() with user input
   - Weak password hashing (MD5/SHA1)
   - Hardcoded credentials

5. **Configuration Issues**
   - Debug mode enabled
   - Insecure session settings
   - Exposed .git directory

### Auto-Fix Capabilities

The auto-fix system can:

- Add `htmlspecialchars()` to prevent XSS
- Inject CSRF tokens into forms
- Replace weak hashing with `password_hash()`
- Add warning comments for dangerous functions
- Disable debug mode in production

All fixes create automatic backups in `storage/backups/security/`.

## Virus Scanner Details

### Detection Methods

1. **Signature Matching**: Compares file hashes against known malware database
2. **Pattern Detection**: Identifies suspicious code patterns
3. **Function Analysis**: Detects malicious function usage
4. **Deep Scanning**: Advanced obfuscation detection

### Threat Types

- **Known Malware**: Files matching malware signatures
- **Malicious Functions**: eval, base64_decode, shell_exec, etc.
- **Suspicious Patterns**: Backdoors, webshells, IRC bots
- **Obfuscated Code**: High obfuscation scores
- **Hidden PHP Code**: PHP in image files

### Quarantine System

Infected files are:
1. Moved to `storage/quarantine/[timestamp]_[filename]`
2. Metadata saved as `.info.json`
3. Original location recorded for restoration

## Test Generation Details

### Auto-Generated Tests

For each component, the system generates:

**Controller Tests**:
- HTTP method tests (GET, POST, PUT, DELETE)
- Authentication tests
- Validation tests
- Error handling tests

**Model Tests**:
- CRUD operation tests
- Relationship tests
- Validation tests
- Edge case tests

**Integration Tests**:
- API endpoint tests
- Database integration tests
- End-to-end workflows

### AI-Powered Generation

When using `--ai` flag:
- Analyzes method signatures and logic
- Generates intelligent test scenarios
- Detects edge cases automatically
- Creates boundary value tests
- Suggests security test cases

## Best Practices

### Security

1. **Run Regular Scans**
   ```bash
   # Weekly security audit
   php oxygen security:scan --fix
   ```

2. **Monitor Reports**
   - Review HTML reports in `storage/reports/`
   - Address critical and high severity issues first

3. **Use Strict Mode in Production**
   ```php
   // config/security.php
   'mode' => env('APP_ENV') === 'production' ? 'strict' : 'balanced',
   ```

### Testing

1. **Generate Tests for New Code**
   ```bash
   php oxygen test:generate --type=unit
   ```

2. **Maintain Coverage**
   ```bash
   php oxygen test:all --coverage
   # Aim for 70%+ coverage
   ```

3. **Run Tests Before Deployment**
   ```bash
   php oxygen test:all
   ```

### Updates

1. **Check for Updates Weekly**
   ```bash
   php oxygen framework:update --check-only
   ```

2. **Always Backup Before Updating**
   ```bash
   php oxygen framework:update --backup
   ```

## Troubleshooting

### Security Scanner Issues

**Problem**: False positives in security scan

**Solution**: Add patterns to whitelist in `config/security.php`:
```php
'whitelist_patterns' => [
    '/your_safe_pattern/i',
],
```

### Test Generation Issues

**Problem**: Tests not generating

**Solution**: Ensure directories exist:
```bash
mkdir -p tests/Unit/Controllers
mkdir -p tests/Unit/Models
mkdir -p tests/Integration
```

### Virus Scanner Issues

**Problem**: Legitimate files quarantined

**Solution**: Restore from quarantine:
```php
$scanner = new \Oxygen\Core\Security\OxygenVirusScanner();
$scanner->restoreFromBackup('[timestamp]_[filename]', 'original/path');
```

## API Reference

### Security Scanner

```php
use Oxygen\Core\Security\OxygenSecurityScanner;

$scanner = new OxygenSecurityScanner([
    'mode' => 'strict',
    'enabled_checks' => ['sql_injection', 'xss'],
]);

$results = $scanner->scanProject(getcwd());
$html = $scanner->generateHtmlReport();
```

### Virus Scanner

```php
use Oxygen\Core\Security\OxygenVirusScanner;

$scanner = new OxygenVirusScanner();
$results = $scanner->scanProject(getcwd(), $deepScan = true, $quarantine = true);
```

### Test Generator

```php
use Oxygen\Core\Testing\OxygenTestGenerator;

$generator = new OxygenTestGenerator($useAI = true);
$tests = $generator->generateAllTests('unit');
```

### Test Runner

```php
use Oxygen\Core\Testing\OxygenTestRunner;

$runner = new OxygenTestRunner();
$results = $runner->runAllTests([
    'types' => ['unit', 'integration'],
    'coverage' => true,
]);
```

## Advanced Usage

### Custom Security Rules

Create custom security rules in `storage/security/security_rules.json`:

```json
{
    "custom_patterns": [
        {
            "pattern": "/your_pattern/i",
            "severity": "high",
            "message": "Custom security issue detected"
        }
    ]
}
```

### Custom Virus Signatures

Add custom malware signatures in `storage/security/virus_signatures.json`:

```json
{
    "known_malware_hashes": [
        "md5_hash_of_malware_file"
    ]
}
```

### Programmatic Test Generation

```php
$generator = new OxygenTestGenerator(true);
$test = $generator->generateControllerTest('UserController', 'path/to/UserController.php');
file_put_contents('tests/Unit/Controllers/UserControllerTest.php', $test);
```

## Performance

- **Security Scan**: ~30 seconds for typical project
- **Virus Scan**: ~1000 files/minute
- **Test Generation**: ~10 tests/minute
- **Test Execution**: Depends on test count

## Support

For issues or questions:
- GitHub: https://github.com/redwan-aouni/oxygen-framework/issues
- Email: aouniradouan@gmail.com

## License

MIT License - See LICENSE file for details
