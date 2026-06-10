#ifndef _RDRANDGET_H_
#define _RDRANDGET_H_


extern int check_rdrand();

extern int get_rand(unsigned int *);
extern unsigned int get_uint();

extern int rdrand16_step(unsigned short int *);
extern int rdseed16_step(unsigned short int *);

extern int rdrand32_step(unsigned int *);
extern int rdseed32_step(unsigned int *);

extern int rdrand64_step(unsigned long long int *);
extern int rdseed64_step(unsigned long long int *);

extern int rdrand_get_uint_retry(unsigned int retry_limit, unsigned int *dest);
extern int rdseed_get_uint_retry(unsigned int retry_limit, unsigned int *dest);
extern int rdrand_get_n_uints_retry(unsigned int n, unsigned int retry_limit, unsigned int *dest);
extern int rdseed_get_n_uints_retry(unsigned int n, unsigned int retry_limit, unsigned int *dest);

extern int rdrand_get_uint_step(unsigned int *dest);
extern int rdseed_get_uint_step(unsigned int *dest);

extern int rdrand_get_n_uints_step(int n, unsigned int *dest);
extern int rdseed_get_n_uints_step(int n, unsigned int *dest);

extern int rdrand_get_bytes_step(unsigned int n, unsigned char *dest);

#endif