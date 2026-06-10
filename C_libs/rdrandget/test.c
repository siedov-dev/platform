#include <stdio.h>
#include <inttypes.h>
#include "rdrandget.h"


int main(int argc, char **argv)
{
    printf("test random\n");

    if (check_rdrand() == 0) {
        fprintf(stderr,"RdRand bit is set\n");
        return 0;
    }

    unsigned int val;
    get_rand(&val);

    printf("%u\n", val);

    rdrand32_step(&val);

    printf("%u\n", val);

    val = get_uint();
    printf("%u\n", val);

    printf("test complete\n");

    return 0;
}