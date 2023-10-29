const BinaryWriter = require("./BinaryWriter");

class Alert {
    constructor(message) {
        this.message = message;
    }
    build(protocol) {
        var writer = new BinaryWriter();
        writer.writeUInt8(69);     // Packet ID
        writer.writeStringZeroUtf8(this.message);

        return writer.toBuffer();
    }
}

module.exports = Alert;
