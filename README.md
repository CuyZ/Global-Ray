Global Ray
==========

This package is abandonned — Spatie now provide their own implementation of that feature, see:

➡️ **[spatie/global-ray](https://github.com/spatie/global-ray)**

Thank you Spatie for hearing our p·**ray**·ers! 🤗

---

<details>
<summary>Show old information</summary>

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

Build the project
-----------------

```bash
$ git clone https://github.com/CuyZ/Global-Ray
$ cd Global-Ray
$ composer install --no-dev
$ composer refresh
```

[Ray]: https://spatie.be/products/ray
</details>

