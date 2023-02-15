# pingMastodon

[![Release](https://img.shields.io/github/v/release/franck-paul/pingMastodon)](https://github.com/franck-paul/pingMastodon/releases)
[![Date](https://img.shields.io/github/release-date/franck-paul/pingMastodon)](https://github.com/franck-paul/pingMastodon/releases)
[![Issues](https://img.shields.io/github/issues/franck-paul/pingMastodon)](https://github.com/franck-paul/pingMastodon/issues)
[![Dotclear](https://img.shields.io/badge/dotclear-v2.24-blue.svg)](https://fr.dotclear.org/download)
[![Dotaddict](https://img.shields.io/badge/dotaddict-official-green.svg)](https://plugins.dotaddict.org/dc2/details/pingMastodon)
[![License](https://img.shields.io/github/license/franck-paul/pingMastodon)](https://github.com/franck-paul/pingMastodon/blob/master/LICENSE)

## Settings

### Mastodon instance

Create a new application in **Development** item of your profile setting using :

For example:

* Application name: **pingMastodon** (you put what you want)
* Website of the application: **<https://open-time.net>** (the URL of your blog)
* Redirection URI: I didn't touch anything and left the default value

And **only** checked the `write:statuses` option and **nothing else**, it is not useful.

You validate and you get three information:

1. ID of the application
1. Secret
1. **Your access token** ← that's what you will need below

### Dotclear

Then on the pingMastodon plugin main settings page:

1. Put your **instance** host (with our without `https://`), example: `mastodon.social`
1. Put your **access token** (see item 3 above)
1. Put a **prefix** which will be inserted at beginning of every statuses (optionnal)

Then activate the plugin and save the settings.
