git commit -a -m "prod deploy"
git push
ssh meme@165.22.93.105 'cd /var/www/todo_project-prod/scripts && sh build.sh'