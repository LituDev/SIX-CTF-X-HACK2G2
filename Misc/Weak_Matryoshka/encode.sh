#!/bin/bash
ENCODED_SCRIPT="script_base.sh"

for i in {1..20}; do
    BASE64_ENCODED=$(base64 $ENCODED_SCRIPT)

    echo "#!/bin/bash" > temp_script.sh
    echo "TEMP_DECODED_FILE=\$(mktemp -t XXXXXXXX)" >> temp_script.sh
    echo "trap 'rm -f \$TEMP_DECODED_FILE' EXIT" >> temp_script.sh
    echo "echo \"$BASE64_ENCODED\" | base64 -d > \$TEMP_DECODED_FILE" >> temp_script.sh
    echo "bash \$TEMP_DECODED_FILE" >> temp_script.sh

    ENCODED_SCRIPT="temp_script.sh"
done

mv temp_script.sh final_script.sh
chmod +x final_script.sh
