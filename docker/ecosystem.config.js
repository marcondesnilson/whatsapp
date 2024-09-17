module.exports = {
    apps: [
        {
            "name": "ClearChatJob",
            "command": "php /var/www/artisan queue:work database --tries=200 --sleep=3 --timeout=900 --queue=ClearChatJob",
            "directory": "/var/www/",
            "instances": 1
        },
        {
            "name": "CountMessageJob",
            "command": "php /var/www/artisan queue:work database --tries=200 --sleep=3 --timeout=900 --queue=CountMessageJob",
            "directory": "/var/www/",
            "instances": 1
        },
        {
            "name": "GroupMemberAddJob",
            "command": "php /var/www/artisan queue:work database --tries=200 --sleep=3 --timeout=900 --queue=GroupMemberAddJob",
            "directory": "/var/www/",
            "instances": 1
        },
        {
            "name": "GroupMemberDeleteJob",
            "command": "php /var/www/artisan queue:work database --tries=200 --sleep=3 --timeout=900 --queue=GroupMemberDeleteJob",
            "directory": "/var/www/",
            "instances": 1
        },
        {
            "name": "GroupMembersJob",
            "command": "php /var/www/artisan queue:work database --tries=200 --sleep=3 --timeout=900 --queue=GroupMembersJob",
            "directory": "/var/www/",
            "instances": 1
        },
        {
            "name": "JoinGroupLinkJob",
            "command": "php /var/www/artisan queue:work database --tries=200 --sleep=3 --timeout=900 --queue=JoinGroupLinkJob",
            "directory": "/var/www/",
            "instances": 1
        },
        {
            "name": "ListarAllChatsJobs",
            "command": "php /var/www/artisan queue:work database --tries=200 --sleep=3 --timeout=900 --queue=ListarAllChatsJobs",
            "directory": "/var/www/",
            "instances": 1
        },
        {
            "name": "OnMessageJob",
            "command": "php /var/www/artisan queue:work database --tries=200 --sleep=3 --timeout=900 --queue=OnMessageJob",
            "directory": "/var/www/",
            "instances": 1
        },
        {
            "name": "OnParticipantsChangedServiceJob",
            "command": "php /var/www/artisan queue:work database --tries=200 --sleep=3 --timeout=900 --queue=OnParticipantsChangedServiceJob",
            "directory": "/var/www/",
            "instances": 1
        },
        {
            "name": "SendMessageJob",
            "command": "php /var/www/artisan queue:work database --tries=200 --sleep=3 --timeout=900 --queue=SendMessageJob",
            "directory": "/var/www/",
            "instances": 1
        },
        {
            "name": "WebhookInSaveJob",
            "command": "php /var/www/artisan queue:work database --tries=200 --sleep=3 --timeout=900 --queue=WebhookInSaveJob",
            "directory": "/var/www/",
            "instances": 1
        }
    ]
}