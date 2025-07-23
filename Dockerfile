FROM php:8.2-cli
WORKDIR /app
COPY . .
RUN chmod +x start.sh
CMD ["bash", "start.sh"]
