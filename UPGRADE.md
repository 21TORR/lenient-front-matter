1.x to 2.0
==========

*   Added new first parameter in constructor of `LenientFrontMatterParser`. If you used a custom separator, pass an empty array as first argument now.

    ```diff
    - $parser = new LenientFrontMatterParser("___+");
    + $parser = new LenientFrontMatterParser([], "___+");
    ```
