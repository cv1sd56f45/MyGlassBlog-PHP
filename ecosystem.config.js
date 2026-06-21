module.exports = {
  apps: [
    {
      name: 'myglassblog-php',
      script: 'docker',
      args: 'compose up -d',
      cwd: '/www/wwwroot/MyGlassBlog-PHP',
      autorestart: false,
      watch: false,
      max_memory_restart: '512M',
      env: {
        NODE_ENV: 'production'
      },
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
      error_file: './logs/pm2-error.log',
      out_file: './logs/pm2-out.log',
      merge_logs: true
    }
  ]
};
