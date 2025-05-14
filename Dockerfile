FROM ubuntu:latest
LABEL authors="maria"

ENTRYPOINT ["top", "-b"]
