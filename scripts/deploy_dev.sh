git commit -a -m "dev deploy"
git push
ssh meme@165.22.93.105 'cd /var/www/todo_project-dev/scripts && sh build.sh'