package ssof.group23;

import org.antlr.v4.runtime.ParserRuleContext;
import ssof.group23.PHPLexer;
import ssof.group23.PHPParser;
import org.antlr.v4.runtime.ANTLRInputStream;
import org.antlr.v4.runtime.CommonTokenStream;

import java.io.File;
import java.io.IOException;
import java.nio.charset.Charset;
import java.nio.file.Files;

public class ParserFacade {

    private static String readFile(File file, Charset encoding) throws IOException {
        byte[] encoded = Files.readAllBytes(file.toPath());
        return new String(encoded, encoding);
    }

    public ParserRuleContext parse(File file) throws IOException {
        String code = readFile(file, Charset.forName("UTF-8"));
        PHPLexer lexer = new PHPLexer(new ANTLRInputStream(code));

        CommonTokenStream tokens = new CommonTokenStream(lexer);

        PHPParser parser = new PHPParser(tokens);

        ParserRuleContext result =parser.htmlDocument();
        System.out.println(result);

        return result;
    }
}
