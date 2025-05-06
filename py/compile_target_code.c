#include <stdio.h>

int a(int _a, int _b) {
    return _a + _b;
}

int main() {
    int c = a(10,20);
    printf("%d", c);
    
    scanf("%d", c);
        
    int limit[10] = {10,};
    
    printf("%d", limit[11]);
}