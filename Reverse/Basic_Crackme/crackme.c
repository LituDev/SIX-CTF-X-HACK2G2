#include <stdint.h>
#include <stdio.h>
#include <string.h>

int check_flag(char* buffer);

int main() {
  char buffer[48];

  printf("Pourras-tu trouver le bon mot de passe ?\n");
  fgets(buffer, sizeof buffer, stdin);

  if (buffer[0] == 'I' && buffer[1] == 'U' && buffer[2] == 'T' && buffer[3] == '{' && check_flag(buffer)) {
    printf("You win ! \n");
  } else {
    printf("You loose... \n");
  }
}
 
int check_flag(char* buffer) {
  uint8_t flag_enc[] = {0x93,0xa4,0xea,0x93,0x9b,0xa1,0xe3,0xa3,0xcf,0xea,0xa4,0xe3,0xcf,0xc3,0xe5,0xa1,0xa6,0xa2,0xe3,0xcf,0xa4,0xe5,0x97,0x9e,0xaa,0xcf,0xef,0xb1}; // cr4ckm3s_4r3_S1mpl3_r1ght_?}
  uint8_t ret = 1;

  if (strlen(buffer) == 33) {
    for (int i=0; i < sizeof flag_enc; i++) {
      if (((buffer[i+4] ^ 0xe3) + 0x13) != flag_enc[i]) {
        ret = 0;
      }
    }
  } else {
    ret = 0;
  }
  return ret;
}
