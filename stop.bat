@echo off
echo Stopping Laravel E-Shop containers...
docker-compose down

echo Removing containers and volumes...
docker-compose down -v

echo Cleanup completed!
