#include <fcntl.h>
#include "./include/log/log.c"

int Log(const char *logPath, int level, const char *file, int line, const char *content) {
    log_set_quiet(1);

    FILE *fp;
    fp = fopen(logPath, "a+");
    if (fp == NULL) {
        return 1;
    }

    int addFpRes;
    addFpRes = log_add_fp(fp, LOG_TRACE);
    if (addFpRes != 0) {
        fclose(fp);
        return addFpRes;
    }

    log_log(level, file, line, content);

    return fclose(fp);
}

//demo
//int main() {
//    return Log(LOG_TRACE, __FILE__, __LINE__, "Hello World");
//}
