const readInputFromPipedString = () => {
    try {
        return JSON.parse(require('fs').readFileSync(0, 'utf8'));
    } catch (e) {
        console.log('Error: invalid json input');
        process.exit(1);
    }
}

const outputToStdOut = (data) => console.log(JSON.stringify(data));


const main = () => {
    const input = readInputFromPipedString();
    outputToStdOut({
        name: input.name || 'Input issue: Not provided name',
        age: 30,
        address: {
            street: 'Main',
            number: 100
        }
    });
}
main();
