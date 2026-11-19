# Changelog

## 7.2 — 2026-11-07

- Cope with RTL

## 7.1 — 2026-08-14

- Exclude password protected entries from ping action
- Enforces some verification before registering module

## 7.0.1 — 2026-08-05

- Cope with ids given by ArrayObject

## 7.0 — 2026-08-03

- Avoid using magic methods for user preferences and blog settings (2.39+)
- Forgot these ones
- Use MetaRecord::strField(), MetaRecord::intField() and MetaRecord::boolField() whenever is possible

## 6.8 — 2026-06-27

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 6.7.1 — 2026-04-06

- Check if input exist before using it

## 6.7 — 2026-03-30

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 6.6 — 2026-03-22

- Add an option to restrict automatic ping to one category only, closes #19
- DA -&gt; DC

## 6.5 — 2025-12-30

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 6.4 — 2025-12-22

- Add an option to enable/disable ping on 1st publication for entries

## 6.3 — 2025-10-29

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 6.2 — 2025-09-22

- Use backend services rather than static calls

## 6.1.1 — 2025-09-13

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 6.1 — 2025-09-13

- Address #15 - add entry catchphrase management

## 6.0 — 2025-09-07

- DC 2.36 compliance

## 5.4 — 2025-08-18

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 5.3.1 — 2025-07-23

- Use category name rather than tag for category options (thanks Tomek), addresses #12

## 5.3 — 2025-07-14

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 5.2.1 — 2025-05-22

- Prefix is not required

## 5.2 — 2025-02-26

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 5.1 — 2025-01-11

- Be conservative (will see in future if something more is needed)

## 5.0 — 2025-01-09

- 2.33 compliance

## 4.10.3 — 2024-06-09

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 4.10.2 — 2024-06-09

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 4.10.1 — 2024-05-29

- Fix description

## 4.10 — 2024-05-25

- Sanitize tags and categories references

## 4.9.1 — 2024-05-24

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 4.9 — 2024-05-24

- Add an option to include categories (post cat' and post cat's parents) as tags, closes #10

## 4.8 — 2024-04-27

- Add a newline between prefix+title, tags and URI, thanks xakan (https://github.com/xakan) for suggestion

## 4.7 — 2024-04-15

- Review tag management

## 4.6 — 2024-04-10

- Switch to camelCase by default (tag conversion)

## 4.5 — 2024-04-10

- Add several tag conversion modes (PascalCase by default)

## 4.4 — 2024-04-09

- Add PascalCase for each word of tag
- Suppression des espaces dans les hashtags

## 4.3.2 — 2024-01-29

- Don't add posts/pages action if plugin not activated

## 4.3.1 — 2024-01-27

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 4.3 — 2024-01-26

- Add an option to include tags

## 4.2 — 2024-01-20

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 4.1 — 2023-10-27

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 4.0 — 2023-10-25

- Use instanced item constants rather than interface ones
- 2.28 compliance

## 3.0 — 2023-08-26

- Dotclear 2.27 compliance, PHP 8.1+

## 2.3.2 — 2023-07-14

- Version de maintenance, sans changement fonctionnel notable relevé dans l’historique.

## 2.3.1 — 2023-06-27

- Add local static $init variable

## 2.3 — 2023-06-25

- And again :-p

## 2.2 — 2023-06-25

- Add posts/pages action to ping Mastodon

## 2.1 — 2023-06-20

- Use dcCore::app()-&gt;adminurl-&gt;redirect() rather than Http::redirect()

## 2.0 — 2023-05-18

- Switch to namespace
- Prepare 2.26
- Add settings documentation in README.md

## 1.0.2 — 2023-02-11

- Fix ping with Scheduled entries

## 1.0.1 — 2023-02-10

- Fix post URL in Mastodon status, thanks @lamecarlate@pouet.it

## 1.0 — 2023-02-09

- Add 1st publication behavior (post only, not other types of entries)
- Add settings
