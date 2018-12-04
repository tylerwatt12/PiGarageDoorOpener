# PiGarageDoorOpener
WiringPi Garage Door Opener in PHP

Built for Raspbian, Apache, PHP, and WiringPi

- Pass in GET variables in a URL, Get back a JSON result that can be used with most APIs, designed for iOS Shortcuts
- Designed for an illuminated arcade button to flash when relay is triggered via web
- Designed for active high relays
- Has debugging functions by passing &addtlInfo=1

USAGE:
LED Arcade button (4 lead style) https://www.adafruit.com/product/3489
Wire LED Ground to a GPIO Pin.
Wire LED Positive to +5V, all LEDs will share this 5v rail.

When GPIO is HIGH, LED is off
When GPIO is LOW, LED is on

Arcade button switch:
Either Switch Pin: Wire to INput on Relay board module
Either Switch Pin: Wire to +5V GPIO on either raspberry pi, or Relay board module

When button is pressed, a mechanical circuit causes the relay to trigger. The raspberry pi does NOT know when these are pressed.

Relay Board Module https://amzn.to/2UboCal
DC+ to +5V
DC- to GPIO GND
IN1 to a GPIO(configurable)
IN2 to a GPIO(configurable)
