export default {
  devices: {
    vendors: {
      fastybird: {
        devices: {
          fastybird_wifi_gw: {
            title: 'FastyBird WiFi Gateway',
          },
          fastybird_3ch_power_strip_r1: {
            title: 'FastyBird Smart Power Strip',
            channels: {
              'socket-one': 'Socket 1',
              'socket-two': 'Socket 2',
              'socket-three': 'Socket 3',
            },
          },
          '8ch_buttons': {
            title: 'FastyBird 8CH Buttons',
            channels: {
              ai: {
                title: 'Buttons',
              },
            },
          },
          '16ch_buttons': {
            title: 'FastyBird 16CH Buttons',
          },
        },
        actions: {
          toggle: 'Toggle {property} state',
          on: 'Turn {property} on',
          off: 'Turn {property} off',
        },
        channels: {
          do: {
            title: 'Digital outputs',
          },
          di: {
            title: 'Digital inputs',
          },
          ao: {
            title: 'Analog outputs',
          },
          ai: {
            title: 'Analog inputs',
          },
        },
        properties: {
          register: {
            title: 'Register {number}',
          },
        },
        configuration: {
          dbldelay: {
            title: 'Double click delay for register {number}',
            description: 'Delay in milliseconds to detect a double click (from 0 to 1000ms).',
          },
          led_mode: {
            button: 'Status indicator mode',
            heading: 'Status indicator mode',
            description: 'Define how the device status indicator should operate. Indicator could be turned off to not disturb you.',
            values: {
              wifi_status: 'Wifi status',
              always_on: 'Always on',
              always_off: 'Always off',
            },
          },
          relays_sync: {
            button: 'Switch synchronization',
            heading: 'Switch sync mode',
            description: 'Define how the different switches should be synchronized.',
            values: {
              disabled: 'Disabled',
              zero_or_one: 'Zero or one active',
              only_one: 'Only one active',
              all_synchronized: 'All synchronized',
            },
          },
          btn_delay: {
            button: 'Double click delay',
            heading: 'Double click delay',
            description: 'Delay in milliseconds to detect a double click (from 0 to 1000ms).',
          },
        },
      },
      itead: {
        devices: {
          sonoff_basic: {
            title: 'Sonoff Smart Switch',
            channels: {
              output: 'Output',
            },
          },
          sonoff_s20: {
            title: 'Sonoff Smart Socket',
            channels: {
              socket: 'Wall Socket',
            },
          },
          sonoff_sc: {
            title: 'Sonoff Environment Unit',
            channels: {
              environment: 'Environment',
            },
          },
          sonoff_pow: {
            title: 'Sonoff Power Meter',
            channels: {
              energy: 'Energy',
            },
          },
        },
        actions: {
          toggle: 'Toggle {property} state',
          on: 'Turn {property} on',
          off: 'Turn {property} off',
        },
        properties: {
          switch: {
            title: 'Switch',
          },
          button: {
            title: 'Button',
          },
          power: {
            title: 'Active power',
          },
          current: {
            title: 'Current',
          },
          voltage: {
            title: 'Voltage',
          },
          apparent: {
            title: 'Apparent power',
          },
          reactive: {
            title: 'Reactive power',
          },
          factor: {
            title: 'Power factor',
          },
          energy: {
            title: 'Energy',
          },
          energy_delta: {
            title: 'Energy (delta)',
          },
          temperature: {
            title: 'Temperature',
          },
          humidity: {
            title: 'Humidity',
          },
          air_quality: {
            title: 'Air quality',
            values: {
              unhealthy: 'Unhealthy',
              moderate: 'Moderate',
              good: 'Good',
            },
          },
          light_level: {
            title: 'Light level',
            values: {
              dusky: 'Dusky',
              normal: 'Normal',
              bright: 'Bright',
            },
          },
          noise_level: {
            title: 'Noise level',
            values: {
              noisy: 'noisy',
              normal: 'Normal',
              quiet: 'Quiet',
            },
          },
        },
        configuration: {
          led_mode: {
            button: 'Status indicator mode',
            heading: 'Status indicator mode',
            description: 'Define how the device status indicator should operate. Indicator could be turned off to not disturb you.',
            values: {
              wifi_status: 'Wifi status',
              always_on: 'Always on',
              always_off: 'Always off',
            },
          },
          relays_sync: {
            button: 'Switch synchronization',
            heading: 'Switch sync mode',
            description: 'Define how the different switches should be synchronized.',
            values: {
              disabled: 'Disabled',
              zero_or_one: 'Zero or one active',
              only_one: 'Only one active',
              all_synchronized: 'All synchronized',
            },
          },
          btn_delay: {
            button: 'Double click delay',
            heading: 'Double click delay',
            description: 'Delay in milliseconds to detect a double click (from 0 to 1000ms).',
          },
          ntp_offset: {
            button: 'Time zone',
            heading: 'Time zone',
            description: 'Define time zone offset in minutes from GMT',
          },
          ntp_server: {
            button: 'NTP server',
            heading: 'NTP server',
            description: 'Define server for time synchronization',
          },
          ntp_region: {
            button: 'DST region',
            heading: 'DST region',
            values: {
              europe: 'Europe',
              usa: 'USA',
            },
          },
          ntp_dst: {
            button: 'Enable DST',
            heading: 'Enable DST',
          },
          on_disconnect: {
            button: 'On disconnect',
            heading: 'On disconnect',
            description: 'State of switch after connection loss to broker',
            values: {
              no_change: 'No change',
              turn_off: 'Turn off',
              turn_on: 'Turn on',
            },
          },
          pulse_mode: {
            button: 'Pulse mode',
            heading: 'Pulse mode',
            values: {
              disabled: 'Disabled',
              normally_off: 'Normally off',
              normally_on: 'Normally on',
            },
          },
          pulse_time: {
            button: 'Pulse time',
            heading: 'Pulse time (s)',
            description: '',
          },
          relay_boot: {
            button: 'Boot mode',
            heading: 'Boot mode',
            description: 'State of switch after boot up',
            values: {
              always_off: 'Always off',
              always_on: 'Always on',
              same_before: 'Same before',
              toggle_before: 'Toggle before',
            },
          },
          sensor_read_interval: {
            button: 'Sensors reading interval',
            heading: 'Sensors reading interval',
            description: 'Select the interval between readings. These will be filtered and averaged for the report',
            values: {
              1: '1 s',
              6: '6 s',
              10: '10 s',
              15: '15 s',
              30: '30 s',
              60: '60 s',
              300: '5 min',
              600: '10 min',
              900: '15 min',
              1800: '30 min',
              3600: '60 min',
            },
          },
          sensor_report_interval: {
            button: 'Sensors report every',
            heading: 'Sensors report every',
            description: 'Select the number of readings to average and report',
          },
          sensor_save_interval: {
            button: 'Sensors save every',
            heading: 'Sensors save every',
            description: 'Save aggregated data to device memory after these many reports. Set it to 0 to disable this feature',
          },
          sensor_power_units: {
            button: 'Power unit',
            heading: 'Power unit',
            values: {
              watts: 'Watts (W)',
              kilowatts: 'Kilowatts (kW)',
            },
          },
          sensor_energy_units: {
            button: 'Energy unit',
            heading: 'Energy unit',
            values: {
              joules: 'Joules (J)',
              kilowatts_hours: 'Kilowatts-hours (kWh)',
            },
          },
          sensor_energy_ration: {
            button: 'Energy ratio',
            heading: 'Energy ratio',
            description: 'Energy ratio in pulses/kWh.',
          },
          sensor_expected_current: {
            button: 'Expected current',
            heading: 'Expected current',
            description: 'In Amperes (A). If you are using a pure resistive load like a bulb, this will be the ratio between the two previous values, i.e. power / voltage. You can also use a current clamp around one of the power wires to get this value.',
          },
          sensor_expected_voltage: {
            button: 'Expected voltage',
            heading: 'Expected voltage',
            description: 'In Volts (V). Enter your the nominal AC voltage for your household or facility, or use multimeter to get this value.',
          },
          sensor_expected_power: {
            button: 'Expected power',
            heading: 'Expected power',
            description: 'In Watts (W). Calibrate your sensor connecting a pure resistive load (like a bulb) and enter here its nominal power or use a multimeter.',
          },
          actions: {
            toggle: 'Toggle {property} state',
            on: 'Turn {property} on',
            off: 'Turn {property} off',
          },
        },
      },
    },
  }
}
