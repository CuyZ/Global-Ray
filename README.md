Global Ray
==========

Use [Ray] in any PHP script without requiring it in your projects.

1. Require this package **globally** with Composer:
   
   `composer global require cuyz/global-ray`

2. Adapt this directive in your PHP configuration:

   ```
   # php.ini
   auto_prepend_file=${HOME}/.composer/vendor/cuyz/global-ray/prepend_ray.php
   ```

3. Use [Ray] inside any PHP script:

   ```php
   ray('Hello world!')->blue();
   ```

[Ray]: https://spatie.be/products/ray
