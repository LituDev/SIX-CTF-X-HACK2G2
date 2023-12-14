FROM ubuntu

# Init
RUN DEBIAN_FRONTEND=noninteractive apt update -y
RUN DEBIAN_FRONTEND=noninteractive apt install -y python3 python3-pip wget snap  apache2 php cron nano libapache2-mod-php && \
    mkdir /usr/app/ && \
    wget https://nodejs.org/dist/v18.16.0/node-v18.16.0-linux-x64.tar.xz -O /node.tar.xz;

WORKDIR / 

# Setup chall modules
RUN tar -xvf node.tar.xz && \
    mv node-v18.16.0-linux-x64 node && \
    ln -s /node/bin/npm /usr/bin/npm && \
    ln -s /node/bin/node /usr/bin/node

# Download and run chrome version to run with puppeteer
RUN wget https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb
RUN apt install /google-chrome-stable_current_amd64.deb -y

# Copy chall data
COPY ./sources/* /var/www/html/
COPY ./bot/ /bot/
RUN mkdir /routine
COPY ./routine/routine.py /routine/routine.py
COPY ./routine/crontab /etc/crontab

# Setup chall data
RUN pip install requests
RUN rm /var/www/html/index.html


# Setup bot
WORKDIR /bot
RUN npm i puppeteer
WORKDIR /

# Start
EXPOSE 80

CMD cron && service apache2 start && tail -f /dev/null