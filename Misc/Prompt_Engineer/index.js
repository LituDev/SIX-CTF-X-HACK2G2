import express from 'express';
import path from 'path';
import bodyParser from 'body-parser';
import { fileURLToPath } from 'url';
import { dirname } from 'path';
import MistralClient from '@mistralai/mistralai';
import fs from 'fs';

const app = express();
const apiKey = process.env.MISTRAL_API_KEY;
const client = new MistralClient(apiKey);
const systemPrompt = fs.readFileSync("./prompt.txt", "utf8");

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

app.use(bodyParser.json());

app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'index.html'));
});

app.post('/ai', async (req, res) => {
    const userContent = req.body.user;
    if (userContent.length > 1000) {
        res.json({ response: "Context too long (>1000)" });
    }

    console.log(userContent);

    try {
        const chatResponse = await client.chat({
            model: 'mistral-tiny',
            messages: [
                { role: "system", content: systemPrompt },
                { role: 'user', content: userContent }
            ],
            max_tokens: 200
        });
        console.log(chatResponse.choices[0].message);

        let responseContent = chatResponse.choices[0].message.content;
        if (userContent.length < 200) {
            responseContent = responseContent.replace("IUT{N0T_T00_0P3N_41}", "IUT{***************}");
        }

        res.json({ response: responseContent });
    } catch (error) {
        res.status(500).json({ error: "Error processing request" });
    }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Server running at http://localhost:${PORT}`);
});
