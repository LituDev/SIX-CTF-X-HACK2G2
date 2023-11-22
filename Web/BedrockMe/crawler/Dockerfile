FROM node

# install chrome  /usr/bin/chromium
RUN apt-get update && apt-get install -y \
    chromium \
    && rm -rf /var/lib/apt/lists/* \

RUN mkdir /app
WORKDIR /app
ADD index.js /app/index.js
ADD package.json /app/package.json
ADD entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

RUN npm install

ENTRYPOINT ["/entrypoint.sh"]