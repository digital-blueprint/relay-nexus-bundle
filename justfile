# Use `just <recipe>` to run a recipe
# https://just.systems/man/en/

# By default, run the `--list` command
default:
    @just --list

# Aliases

alias fmt := format

# Format all files
[group('linter')]
format args='':
    nix-shell -p treefmt just nodePackages.prettier nixfmt-rfc-style statix taplo php83Packages.php-cs-fixer --run "treefmt {{ args }}"

# Run linters on all files
[group('linter')]
lint:
    nix-shell -p php83Packages.composer --run "composer run lint"
