package ssof.group23;

/**
 * Created by miguel on 11/23/16.
 */

import org.antlr.v4.runtime.ParserRuleContext;
import org.tempura.console.util.Ansi;

import java.io.File;
import java.io.IOException;
import java.nio.charset.Charset;
import java.nio.file.Files;

public class Application {
    public static void main(String[] args) throws IOException {
        boolean printAST = true; //For debug purpuses

        if(args.length == 0) {
            System.out.println(Ansi.HIGH_INTENSITY + Ansi.RED + "Usage ./gradlew [build] run -Dexec.args=\"sliceSourcePath\"" + Ansi.LOW_INTENSITY + Ansi.BLACK);
            System.exit(1);
        }

        //Getting file
        File file = new File(args[0]);

        //Creating ast
        ParserFacade parserFacade = new ParserFacade();
        ParserRuleContext ast = parserFacade.parse(file);

        if(printAST) {
            String code = readFile(file, Charset.forName("UTF-8"));
            System.out.println(code);

            AstPrinter astPrinter = new AstPrinter();
            astPrinter.print(ast);
        }

        //Now the juicy part
        VulnerabilityDetector detector = new VulnerabilityDetector(ast);
        detector.detect(); //Do your thing
    }
    private static String readFile(File file, Charset encoding) throws IOException {
        byte[] encoded = Files.readAllBytes(file.toPath());
        return new String(encoded, encoding);
    }
}