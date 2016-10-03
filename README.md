xfive.co starter theme for WordPress to work with chisel-generator and Timber WP plugin.

If you want to disable manifest file based asset path generation in assetPath function added to Twig you need to set Environment variable called WP_ENV_DEV.

In Apache2 conf file:
`SetEnv WP_ENV_DEV`
