FROM node

# install chrome  /usr/bin/chromium
RUN apt-get update && apt-get install -y \
    chromium \
    && rm -rf /var/lib/apt/lists/*

RUN mkdir /app
WORKDIR /app

ADD package.json /app/package.json
RUN npm install

ADD bot.js /app/bot.js

ENTRYPOINT ["node", "/app/bot.js"]