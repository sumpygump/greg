# Greg : Greg Regularly Evokes Goals

Greg is a CLI program that helps you set and be reminded of goals.

## Installation

This program is written in PHP and requires PHP CLI version 7.1 or greater.

Use `composer global require sumpygump/greg` to install it globally on your machine.

You could also clone the repo and symlink `bin/greg` in the project directory to a directory that is in your `$PATH`.

## Usage

Use `greg help` to get help.

```
Greg Regularly Evokes Goals
 ▄▄  ▄▄▄   ▄▄   ▄▄
█  █ █  ▀ █▄▄█ █  █
▀▄▄█ █    ▀▄▄▄ ▀▄▄█
▄▄▄▀           ▄▄▄▀

Usage:
  greg <cmd> [arguments]
Commands:
  help        Show this help message
  list        Show list of goals
  add <goal>  Add a new goal
  remind      Show goal reminder for today
  complete    Mark a goal as complete
  version     Show version of greg
```
