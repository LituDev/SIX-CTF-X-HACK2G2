FROM node:bookworm-slim

WORKDIR /app

COPY package*.json ./

RUN npm install

COPY . .

ENV PORT=80
ENV MISTRAL_API_KEY=lAgWCL44A48XdgjLMBce8u4855qzmaZI

EXPOSE 80

CMD ["npm", "start"]
