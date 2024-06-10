Nette Project
=================

Postup pro nasazení projektu:
 - natažení knihoven: "composer install" v kořenové složce volání v konsoli
 - nastavení připojení k db v app/core/config/config.local.neon
 - v kořenovém adresáři vytvořit složky 'temp' a 'log' s právami na zápis
 - natažení doctrine entit - v složce www přes konzoli příkaz: "php index.php orm:schema-tool:update --force"
 - do databáze je třeba ručně vložit tyto záznamy:
        INSERT INTO `permision_group` (`id`, `name`, `is_hidden`) VALUES (1, 'Super-administrator',1);
        INSERT INTO `user` (`id`, `group_id`, `name`, `password`, `email`, `login`, `active`, `last_logon`, `is_hidden`) VALUES
        (1, 1, 'admin', '$2y$10$V6Uoe8oCfwJNHEyIp97SQ.1iYzDkFibVFhZvYzD7yxrkfNK/vUp3K', 'jindra@jindra.cz', 'admin', 1, NULL, 0);
- přihlašování do administrace nyní bude login: admin, heslo: admin

- pro rozjetí Kdyby/Translation je třeba ještě upravit stažené knihovny o patch, který není v main větvi, protože není jinak kompaktibilní s aktuálním Nette:
    https://github.com/Kdyby/Translation/commit/8c9aa8174610b8c05523d96d2a8bda0731a1e62f
    jedná se o soubor: /vendor/kdyby/translation/src/Kdyby/Translation/Latte/TranslateMacros.php a metodu macroTranslate( ...

Requirements
------------

- PHP 5.6 or higher.
- MySQL,
- Composer
