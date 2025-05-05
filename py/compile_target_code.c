#include <stdio.h>

void set_value(char *str) {
    *str = 'A';
}

int main() {
    int arr[5];
    arr[5] = 100;  // 배열 인덱스 초과
    int num;
    scanf("%lf", &num);  // 형식 지정자 오류 (int에 %lf)
    int id = 42;
    set_value(&id);  // 포인터 타입 불일치 (int* → char*)
    return 0;
}