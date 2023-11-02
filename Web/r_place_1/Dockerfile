FROM rust:1.73-slim as builder 

RUN apt-get update && \
    apt-get install -y pkg-config libssl-dev && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . .

RUN cargo build --release

FROM debian:bookworm-slim

RUN apt-get update && \
    apt-get install -y libssl-dev ca-certificates && \
    update-ca-certificates && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . .
COPY --from=builder /app/target/release/r-place-1 .

ENV WIDTH=128
ENV HEIGHT=128
ENV COOLDOWN_SEC=10
ENV UPDATE_COOLDOWN_SEC=60
ENV BIND_ADDRESS=0.0.0.0
ENV PORT=8080
ENV SERVE_STATIC=true
ENV SMTP_SERVER=smtp.office365.com
ENV SMTP_PORT=587
ENV SMTP_USER=place.vannes.verif@outlook.com
ENV SMTP_PASSWORD=nop
ENV URL=http://localhost:8080
ENV FLAG=IUT{P05T_M3_H4RD3R_D4DDY}
ENV BASE_IMAGE=base.png

EXPOSE 8080

CMD ["./r-place-1"]
