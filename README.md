# cog-phpunit-rerun

Enables rerunning failed tests.


## Installation

Include in your main composer:

"repositories": [
  {
    "type": "vcs",
    "url": "git@github.com:KarlCOG/cog-phpunit-rerun.git"
  }
]

and

"require-dev": {
  "cog-phpunit-rerun": "dev-master"
}

and

"scripts": {
  "post-update-cmd": [
    "ln -s vendor/cog-phpunit-rerun/phpunit new-phpunit",
    "mv new-phpunit phpunit"
  ],
  "post-install-cmd": [
    "ln -s vendor/cog-phpunit-rerun/phpunit new-phpunit",
    "mv new-phpunit phpunit"
  ]
}
