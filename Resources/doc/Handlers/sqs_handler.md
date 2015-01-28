# SQS Handler - Monolog Extra Bundle

## Prerequisite

- Install & Configure SQSBundle. ([see here](https://github.com/lelivrescolaire/SQSBundle))
- Create a Queue on AWS.
- Configure the queue with SQSBundle. ([see here](https://github.com/lelivrescolaire/SQSBundle))
- Configure the SQS Handler with this bundle:

```yml
lls_monolog_extra:
    handlers:           # Create custom handlers
        sqs_handler:
            type: sqs
            queue: myQueue  # Queue identifier from SQSBundle
            level: INFO     # Log level (int or label)
            bubble: true    # Whether or not execute next handlers
```

- Configure Monolog to use your new handler

```yml
monolog:
    handlers:
        sqs:
            type:     service
            id:       lls_monolog_extra.handlers.sqs_handler  # Auto generated service
            priority: 0
```

- Enjoy :)