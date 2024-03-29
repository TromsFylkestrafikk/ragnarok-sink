# ragnarok-sink

This is an API only package used by Ragnarok and data sinks used by it.

All data sinks for Ragnarok must extend the `DataSink` abstraction
to be available in Ragnarok.

## Install

```bash
composer require ragnarok/sink
```

## Writing sinks

In a full installation, this package provides a command
`ragnarok:make-sink` which creates a stub laravel composer package
with the basic Ragnarok Sink API in place. Use this a starting point
when creating new sinks.

## License

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or (at
your option) any later version.

This program is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <https://www.gnu.org/licenses/>.
